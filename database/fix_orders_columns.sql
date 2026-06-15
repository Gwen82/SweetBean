alter table public.orders
    add column if not exists user_id uuid,
    add column if not exists method text not null default 'delivery',
    add column if not exists address text not null default '',
    add column if not exists subtotal numeric(10, 2) not null default 0,
    add column if not exists delivery_fee numeric(10, 2) not null default 0,
    add column if not exists total_price numeric(10, 2) not null default 0,
    add column if not exists status text not null default 'Pending',
    add column if not exists created_at timestamptz not null default now(),
    add column if not exists updated_at timestamptz not null default now();

do $$
begin
    if not exists (
        select 1 from pg_constraint
        where conname = 'orders_method_check'
          and conrelid = 'public.orders'::regclass
    ) then
        alter table public.orders
            add constraint orders_method_check check (method in ('delivery', 'pickup'));
    end if;

    if not exists (
        select 1 from pg_constraint
        where conname = 'orders_status_check'
          and conrelid = 'public.orders'::regclass
    ) then
        alter table public.orders
            add constraint orders_status_check check (status in ('Pending', 'Processing', 'Delivering', 'Completed'));
    end if;
end;
$$;

alter table public.orders
    drop constraint if exists orders_user_id_fkey;

alter table public.orders
    add constraint orders_user_id_fkey
    foreign key (user_id) references public.sweetbean_users(id) on delete cascade;

create table if not exists public.order_items (
    id bigserial primary key,
    order_id bigint,
    menu_id text not null default '',
    price numeric(10, 2) not null default 0,
    qty integer not null default 1,
    created_at timestamptz not null default now()
);

alter table public.order_items
    add column if not exists order_id bigint,
    add column if not exists menu_id text not null default '',
    add column if not exists price numeric(10, 2) not null default 0,
    add column if not exists qty integer not null default 1,
    add column if not exists created_at timestamptz not null default now();

alter table public.order_items
    drop constraint if exists order_items_menu_id_fkey;

alter table public.order_items
    alter column menu_id type text using menu_id::text,
    alter column menu_id set default '',
    alter column menu_id set not null;

do $$
begin
    if not exists (
        select 1 from pg_constraint
        where conname = 'order_items_order_id_fkey'
          and conrelid = 'public.order_items'::regclass
    ) then
        alter table public.order_items
            add constraint order_items_order_id_fkey
            foreign key (order_id) references public.orders(id) on delete cascade;
    end if;

    if not exists (
        select 1 from pg_constraint
        where conname = 'order_items_qty_check'
          and conrelid = 'public.order_items'::regclass
    ) then
        alter table public.order_items
            add constraint order_items_qty_check check (qty > 0);
    end if;
end;
$$;

create index if not exists orders_user_id_idx
    on public.orders (user_id);

create index if not exists order_items_order_id_idx
    on public.order_items (order_id);
