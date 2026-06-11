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
