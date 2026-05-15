INSERT INTO users (username, real_name, email, password_hash, role, status, email_verified_at)
VALUES (
  'admin',
  '系統管理者',
  'admin@example.com',
  '$2y$10$d.hvc2dpGZhKZfDdU4TW.OPWxx5PGcBAzqpkJfPXMhfJiriP143vO',
  'admin',
  'active',
  NOW()
);
