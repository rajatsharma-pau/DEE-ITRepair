-- Optional one-time cleanup for existing users whose users.college_id / users.department_id are blank.
-- Run this in phpMyAdmin after taking a backup.

UPDATE users u
JOIN employees e ON e.user_id = u.id
SET
    u.college_id = COALESCE(u.college_id, e.college_id),
    u.department_id = COALESCE(u.department_id, e.department_id)
WHERE (u.college_id IS NULL OR u.department_id IS NULL);
