create extension if not exists pgcrypto;

create table if not exists public.sweetbean_users (
    id uuid primary key default gen_random_uuid(),
    full_name text not null,
    email text not null unique,
    phone text not null unique,
    birth_date date,
    password text not null,
    role text not null default 'customer',
    reset_token text,
    reset_token_expires_at timestamptz,
    created_at timestamptz not null default now(),
    updated_at timestamptz not null default now()
);

create index if not exists sweetbean_users_reset_token_idx
    on public.sweetbean_users (reset_token);

create or replace function public.set_updated_at()
returns trigger
language plpgsql
as $$
begin
    new.updated_at = now();
    return new;
end;
$$;

drop trigger if exists set_sweetbean_users_updated_at on public.sweetbean_users;

create trigger set_sweetbean_users_updated_at
before update on public.sweetbean_users
for each row
execute function public.set_updated_at();

create table if not exists public.orders (
    id bigserial primary key,
    user_id uuid not null,
    method text not null check (method in ('delivery', 'pickup')),
    address text not null,
    subtotal numeric(10, 2) not null default 0,
    delivery_fee numeric(10, 2) not null default 0,
    total_price numeric(10, 2) not null default 0,
    status text not null default 'Pending' check (status in ('Pending', 'Processing', 'Delivering', 'Completed')),
    created_at timestamptz not null default now(),
    updated_at timestamptz not null default now()
);

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

create index if not exists orders_user_id_idx
    on public.orders (user_id);

drop trigger if exists set_orders_updated_at on public.orders;

create trigger set_orders_updated_at
before update on public.orders
for each row
execute function public.set_updated_at();

create table if not exists public.order_items (
    id bigserial primary key,
    order_id bigint not null references public.orders(id) on delete cascade,
    menu_id text not null,
    price numeric(10, 2) not null,
    qty integer not null check (qty > 0),
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

create index if not exists order_items_order_id_idx
    on public.order_items (order_id);
