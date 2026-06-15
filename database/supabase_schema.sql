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

create table if not exists public.menu_items (
    id text primary key,
    name text not null,
    category text not null,
    price numeric(10, 2) not null check (price >= 0),
    description text not null default '',
    badge text not null default '',
    icon text not null default 'fa-mug-hot',
    is_available boolean not null default true,
    sort_order integer not null default 0,
    created_at timestamptz not null default now(),
    updated_at timestamptz not null default now()
);

alter table public.menu_items
    add column if not exists name text not null default '',
    add column if not exists category text not null default '',
    add column if not exists price numeric(10, 2) not null default 0,
    add column if not exists description text not null default '',
    add column if not exists badge text not null default '',
    add column if not exists icon text not null default 'fa-mug-hot',
    add column if not exists is_available boolean not null default true,
    add column if not exists sort_order integer not null default 0,
    add column if not exists created_at timestamptz not null default now(),
    add column if not exists updated_at timestamptz not null default now();

insert into public.menu_items (id, name, category, price, description, badge, icon, is_available, sort_order)
values
    ('classic-espresso', 'Classic Espresso', 'Drinks', 3.50, 'A bold, smooth single shot with a caramel finish.', 'Best Seller', 'fa-mug-saucer', true, 10),
    ('vanilla-latte', 'Vanilla Latte', 'Drinks', 4.75, 'Steamed milk, espresso, and house vanilla syrup.', 'Customer Pick', 'fa-mug-hot', true, 20),
    ('iced-caramel-macchiato', 'Iced Caramel Macchiato', 'Drinks', 5.25, 'Chilled espresso layered with milk and caramel drizzle.', 'Best Seller', 'fa-glass-water', true, 30),
    ('mocha-frappe', 'Mocha Frappe', 'Drinks', 5.50, 'Blended coffee, cocoa, whipped cream, and chocolate.', 'Cold', 'fa-blender', true, 40),
    ('strawberry-shortcake', 'Strawberry Shortcake', 'Cakes', 6.25, 'Soft sponge cake with fresh strawberries and cream.', 'Fresh', 'fa-cake-candles', true, 50),
    ('chocolate-ganache-cake', 'Chocolate Ganache Cake', 'Cakes', 6.75, 'Rich chocolate layers finished with glossy ganache.', 'Best Seller', 'fa-cake-candles', true, 60),
    ('butter-croissant', 'Butter Croissant', 'Pastries', 3.25, 'Flaky, golden pastry baked fresh each morning.', 'Baked Today', 'fa-cookie-bite', true, 70),
    ('cinnamon-roll', 'Cinnamon Roll', 'Pastries', 4.00, 'Warm cinnamon swirl topped with vanilla glaze.', 'Warm', 'fa-bread-slice', true, 80),
    ('blueberry-muffin', 'Blueberry Muffin', 'Pastries', 3.75, 'Tender muffin packed with blueberries and crumb topping.', 'Fresh', 'fa-cookie', true, 90)
on conflict (id) do update set
    name = excluded.name,
    category = excluded.category,
    price = excluded.price,
    description = excluded.description,
    badge = excluded.badge,
    icon = excluded.icon,
    is_available = excluded.is_available,
    sort_order = excluded.sort_order,
    updated_at = now();

create index if not exists menu_items_category_idx
    on public.menu_items (category);

drop trigger if exists set_menu_items_updated_at on public.menu_items;

create trigger set_menu_items_updated_at
before update on public.menu_items
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
        where conname = 'order_items_menu_id_fkey'
          and conrelid = 'public.order_items'::regclass
    ) then
        alter table public.order_items
            add constraint order_items_menu_id_fkey
            foreign key (menu_id) references public.menu_items(id);
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
