import { Pool } from 'pg';

const pool = new Pool({
  user: 'nerdery_db',
  host: 'localhost',
  database: 'nerdery',
  password: 'nerdery_password',
  port: 5432,
});

export default pool;