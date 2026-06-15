// Campus Lens user service database migration script.
require('dotenv').config();

const fs = require('fs/promises');
const path = require('path');
const { pool } = require('../config/db');

async function migrate() {
  const migrationPath = path.join(__dirname, '..', '..', 'init.sql');
  const sql = await fs.readFile(migrationPath, 'utf8');

  await pool.query(sql);
  await pool.end();

  console.log('Migration completed');
}

migrate().catch(async (error) => {
  console.error('Migration failed', error);
  await pool.end();
  process.exit(1);
});
