import { transformUsers } from './index';
import bcrypt from 'bcrypt';
import { LegacyUser } from './index';

jest.mock('bcrypt', () => ({
    hash: jest.fn().mockResolvedValue('hashed_password'),
}));

describe('transformUsers', () => {
    it('should transform users correctly', async () => {
        const legacyUsers: LegacyUser[] = [
            { UserID: 'user1', UserPassword: 'password1', CoolPoints: 10 },
            { UserID: 'user2', UserPassword: 'password2', CoolPoints: 20 },
        ];

        const transformedUsers = await transformUsers(legacyUsers);

        expect(transformedUsers).toEqual([
            {
                username: 'user1',
                email: 'user1@nerdery.com',
                password_hash: 'hashed_password',
                cool_points: 10,
            },
            {
                username: 'user2',
                email: 'user2@nerdery.com',
                password_hash: 'hashed_password',
                cool_points: 20,
            },
        ]);

        expect(bcrypt.hash).toHaveBeenCalledWith('password1', 10);
        expect(bcrypt.hash).toHaveBeenCalledWith('password2', 10);
    });
});