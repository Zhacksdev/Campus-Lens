import 'dotenv/config';
import { readFile } from 'node:fs/promises';
import { dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';
import { db, closeDb } from '../src/db/index.js';

const __dirname = dirname(fileURLToPath(import.meta.url));
const initSqlPath = resolve(__dirname, '..', 'init.sql');

try {
  const sql = await readFile(initSqlPath, 'utf8');
  await db.query(sql);
  console.log('Database migration completed');
} finally {
  await closeDb();
}
