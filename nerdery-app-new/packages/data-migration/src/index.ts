import mysql from 'mysql2/promise';
import { Pool } from 'pg';
import bcrypt from 'bcrypt';

export interface LegacyUser {
    UserID: string;
    UserPassword: string;
    CoolPoints: number;
}

export const getLegacyUsers = async (mysqlConnection: mysql.Connection): Promise<LegacyUser[]> => {
    const [rows] = await mysqlConnection.execute('SELECT * FROM Users');
    return rows as LegacyUser[];
};

export const transformUsers = async (legacyUsers: LegacyUser[]) => {
    const transformedUsers = [];
    for (const user of legacyUsers) {
        const hashedPassword = await bcrypt.hash(user.UserPassword, 10);
        transformedUsers.push({
            username: user.UserID,
            email: `${user.UserID}@nerdery.com`,
            password_hash: hashedPassword,
            cool_points: user.CoolPoints || 0,
        });
    }
    return transformedUsers;
};

export const loadUsers = async (pgPool: Pool, users: any[]) => {
    for (const user of users) {
        await pgPool.query(
            'INSERT INTO users (username, email, password_hash, cool_points) VALUES ($1, $2, $3, $4)',
            [user.username, user.email, user.password_hash, user.cool_points]
        );
    }
};

async function main() {
    const mysqlConnection = await mysql.createConnection({
        host: 'localhost',
        user: 'root',
        password: '',
        database: 'nerdery'
    });

    const pgPool = new Pool({
        user: 'nerdery_db',
        host: 'localhost',
        database: 'nerdery',
        password: 'nerdery_password',
        port: 5432,
    });

    try {
        console.log('Starting user migration...');

        const legacyUsers = await getLegacyUsers(mysqlConnection);
        console.log(`Found ${legacyUsers.length} users to migrate.`);

        const transformedUsers = await transformUsers(legacyUsers);

        await loadUsers(pgPool, transformedUsers);

        console.log('User migration completed successfully!');

    } catch (error) {
        console.error('Error during migration:', error);
    } finally {
        await mysqlConnection.end();
        await pgPool.end();
    }
}

if (require.main === module) {
    main();
}
