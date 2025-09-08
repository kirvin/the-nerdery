import mysql from 'mysql2/promise';
import { Pool } from 'pg';
import bcrypt from 'bcrypt';

async function main() {
    // Legacy MySQL DB Connection
    const mysqlConnection = await mysql.createConnection({
        host: 'localhost', // Replace with actual host
        user: 'root', // Replace with actual user
        password: '', // Replace with actual password
        database: 'nerdery' // Replace with actual database
    });

    // New PostgreSQL DB Connection
    const pgPool = new Pool({
        user: 'nerdery_db',
        host: 'localhost',
        database: 'nerdery',
        password: 'nerdery_password',
        port: 5432,
    });

    try {
        console.log('Starting user migration...');

        // 1. Extract users from legacy DB
        const [legacyUsers] = await mysqlConnection.execute('SELECT * FROM Users');

        console.log(`Found ${legacyUsers.length} users to migrate.`);

        // 2. Transform and Load users into new DB
        for (const user of legacyUsers) {
            // 2a. Hash the password
            const hashedPassword = await bcrypt.hash(user.UserPassword, 10);

            // 2b. Insert into new users table
            await pgPool.query(
                'INSERT INTO users (username, email, password_hash, cool_points) VALUES ($1, $2, $3, $4)',
                [user.UserID, `${user.UserID}@nerdery.com`, hashedPassword, user.CoolPoints || 0] // Assuming email can be derived from UserID for the PoC
            );
            console.log(`Migrated user: ${user.UserID}`);
        }

        console.log('User migration completed successfully!');

    } catch (error) {
        console.error('Error during migration:', error);
    } finally {
        await mysqlConnection.end();
        await pgPool.end();
    }
}

main();
