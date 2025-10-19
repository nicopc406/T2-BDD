# T2-BDD

A simple PHP and MySQL CRUD (Create, Read, Update, Delete) application for managing users.

## Features

- ✅ Create new users
- ✅ View all users in a table
- ✅ Update existing users
- ✅ Delete users
- ✅ Clean and responsive UI
- ✅ MySQL database integration

## Requirements

- PHP 7.0 or higher
- MySQL 5.6 or higher
- Web server (Apache, Nginx, or PHP built-in server)

## Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/nicopc406/T2-BDD.git
   cd T2-BDD
   ```

2. **Set up the database**
   
   Open MySQL and run the SQL commands from `schema.sql`:
   ```bash
   mysql -u root -p < schema.sql
   ```
   
   Or manually create the database:
   - Create a database named `testdb`
   - Run the SQL commands in `schema.sql`

3. **Configure database connection**
   
   Edit `config.php` if needed to match your MySQL credentials:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   define('DB_NAME', 'testdb');
   ```

4. **Start the application**
   
   Using PHP built-in server:
   ```bash
   php -S localhost:8000
   ```
   
   Or configure your web server to serve the files from this directory.

5. **Access the application**
   
   Open your browser and navigate to:
   - `http://localhost:8000` (if using PHP built-in server)
   - Or your configured web server URL

## Usage

### Adding a User
1. Fill in the "Name" and "Email" fields in the form
2. Click "Add User" button
3. The new user will appear in the table below

### Editing a User
1. Click the "Edit" button next to a user in the table
2. Modify the name or email in the form
3. Click "Update User" to save changes

### Deleting a User
1. Click the "Delete" button next to a user in the table
2. Confirm the deletion when prompted
3. The user will be removed from the database

## File Structure

```
T2-BDD/
├── index.php       # Main application file with CRUD operations
├── config.php      # Database configuration
├── schema.sql      # Database schema and sample data
└── README.md       # This file
```

## Database Schema

The application uses a simple `users` table with the following structure:

| Column     | Type         | Description                    |
|------------|--------------|--------------------------------|
| id         | INT          | Primary key (auto-increment)   |
| name       | VARCHAR(100) | User's name                    |
| email      | VARCHAR(100) | User's email address           |
| created_at | TIMESTAMP    | Record creation timestamp      |

## Security Notes

This is a basic demonstration application. For production use, consider:
- Using prepared statements to prevent SQL injection
- Implementing user authentication
- Adding input validation and sanitization
- Using HTTPS for secure communication
- Storing database credentials in environment variables

## License

This project is open source and available under the MIT License.