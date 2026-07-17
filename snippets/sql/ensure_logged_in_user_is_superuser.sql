-- Optional check/fix only if your own login lost superuser role after testing.
-- Replace YOUR_PHONE_HERE with the logged-in Superuser phone number.

INSERT INTO roles (name, slug, display_name, description, is_active, created_at, updated_at)
SELECT 'superuser', 'superuser', 'Superuser', 'Full university level access', 1, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM roles WHERE name = 'superuser' OR slug = 'superuser');

UPDATE users
SET role = 'superuser'
WHERE phone = 'YOUR_PHONE_HERE';

INSERT INTO user_roles (user_id, role_id, college_id, department_id, is_primary, created_at, updated_at)
SELECT u.id, r.id, NULL, NULL, 1, NOW(), NOW()
FROM users u
JOIN roles r ON r.name = 'superuser' OR r.slug = 'superuser'
WHERE u.phone = 'YOUR_PHONE_HERE'
AND NOT EXISTS (
    SELECT 1 FROM user_roles ur
    WHERE ur.user_id = u.id AND ur.role_id = r.id
);
