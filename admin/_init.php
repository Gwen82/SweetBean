<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../config/db.php';

if (($_SESSION['user_role'] ?? '') !== 'admin') {
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit;
}

function h($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function money($value): string
{
    return '$' . number_format((float) $value, 2);
}

function admin_flash(?string $message = null, string $type = 'success'): ?array
{
    if ($message !== null) {
        $_SESSION['admin_flash'] = ['message' => $message, 'type' => $type];
        return null;
    }

    $flash = $_SESSION['admin_flash'] ?? null;
    unset($_SESSION['admin_flash']);
    return $flash;
}

function admin_redirect(string $path): void
{
    header('Location: ' . BASE_URL . 'admin/' . $path);
    exit;
}

function admin_statuses(): array
{
    return ['Pending', 'Processing', 'Delivering', 'Completed'];
}

function admin_ensure_reviews_table(PDO $conn): void
{
    $conn->exec("
        CREATE TABLE IF NOT EXISTS public.reviews (
            id bigserial PRIMARY KEY,
            user_id uuid REFERENCES public.sweetbean_users(id) ON DELETE SET NULL,
            order_id bigint REFERENCES public.orders(id) ON DELETE SET NULL,
            rating integer NOT NULL DEFAULT 5 CHECK (rating BETWEEN 1 AND 5),
            comment text NOT NULL DEFAULT '',
            is_visible boolean NOT NULL DEFAULT true,
            created_at timestamptz NOT NULL DEFAULT now(),
            updated_at timestamptz NOT NULL DEFAULT now()
        )
    ");

    $conn->exec("
        DO $$
        BEGIN
            IF NOT EXISTS (
                SELECT 1 FROM pg_trigger
                WHERE tgname = 'set_reviews_updated_at'
            ) THEN
                CREATE TRIGGER set_reviews_updated_at
                BEFORE UPDATE ON public.reviews
                FOR EACH ROW
                EXECUTE FUNCTION public.set_updated_at();
            END IF;
        END;
        $$
    ");
}

admin_ensure_reviews_table($conn);
