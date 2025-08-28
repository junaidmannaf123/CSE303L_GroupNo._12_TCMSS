# TCMSS Database Setup Guide

## Overview
This guide explains how to set up the database for the Tortoise Conservation Management System (TCMSS).

## Prerequisites
- XAMPP (or similar local server with MySQL)
- PHP 7.4 or higher
- MySQL 5.7 or higher

## Database Setup Steps

### 1. Start XAMPP
- Start Apache and MySQL services in XAMPP Control Panel

### 2. Create Database
1. Open phpMyAdmin: http://localhost/phpmyadmin
2. Click "New" to create a new database
3. Enter database name: `tccms`
4. Click "Create"

### 3. Import Database Structure
1. In the `tccms` database, click "Import"
2. Click "Choose File" and select `tccms.sql` from this directory
3. Click "Go" to import the database structure and sample data

### 4. Verify Tables
After import, you should see these tables:
- `tbltortoise` - Main tortoise records
- `tblspecies` - Tortoise species information
- `tblenclosure` - Enclosure details
- `tblbreedingrecord` - Breeding records
- `tbleggdetails` - Egg details
- `tblstaffmember` - Staff information
- And several other related tables

### 5. Test the System
1. Navigate to `tortoise-list.php` in your browser
2. The page should now display real data from the database
3. You can add, edit, and delete tortoise records

## Database Configuration

### Connection Details
The system uses these default connection settings (in `config/database.php`):
- Host: `localhost`
- Database: `tccms`
- Username: `root`
- Password: `` (empty for XAMPP default)

### Customizing Connection
If you need to change the database connection:
1. Edit `config/database.php`
2. Update the connection parameters as needed

## API Endpoints

The system includes these API endpoints:
- `api/get_tortoises.php` - Fetch all tortoises
- `api/add_tortoise.php` - Add new tortoise
- `api/update_tortoise.php` - Update existing tortoise
- `api/delete_tortoise.php` - Delete tortoise

## Troubleshooting

### Common Issues

1. **Connection Failed**
   - Ensure MySQL is running in XAMPP
   - Check database name is correct
   - Verify username/password

2. **Tables Not Found**
   - Ensure `tccms.sql` was imported successfully
   - Check database name matches

3. **Permission Errors**
   - Ensure PHP has read/write access to the directory
   - Check file permissions

### Error Logs
- Check XAMPP error logs: `xampp/apache/logs/error.log`
- Check PHP error logs in your browser's developer console

## Sample Data

The imported database includes:
- 30 tortoise records with real names
- 4 species types
- 3 enclosure locations
- Sample breeding and measurement records

## Support

If you encounter issues:
1. Check the error messages in the browser console
2. Verify database connection settings
3. Ensure all required tables exist
4. Check file permissions for the API directory
