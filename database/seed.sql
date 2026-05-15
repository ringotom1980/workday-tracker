INSERT INTO users (username, real_name, email, password_hash, role, status, email_verified_at)
VALUES (
  'admin',
  'Admin',
  'admin@example.com',
  '$2y$10$d.hvc2dpGZhKZfDdU4TW.OPWxx5PGcBAzqpkJfPXMhfJiriP143vO',
  'admin',
  'active',
  NOW()
)
ON DUPLICATE KEY UPDATE
  real_name = VALUES(real_name),
  password_hash = VALUES(password_hash),
  role = VALUES(role),
  status = VALUES(status),
  email_verified_at = COALESCE(email_verified_at, VALUES(email_verified_at));
