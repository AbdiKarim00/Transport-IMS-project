# Database Schema Fix - Missing service_providers Table

## Issue Description

Your Laravel Transport Management System is throwing the following error:

```
SQLSTATE[42P01]: Undefined table: 7 ERROR: relation "service_providers" does not exist
```

## Root Cause

The application code includes a `ServiceProvider` model (`app/Models/ServiceProvider.php`) that expects a `service_providers` table in the database, but this table was never created. The table is referenced in:

- `VehicleAnalyticsController` (line 64) - `ServiceProvider::count()`
- `MaintenanceSchedule` model - relationship to ServiceProvider
- `IncidentRepair` model - relationship to ServiceProvider

## Solution

### Option 1: Quick Fix (Recommended)

Run the provided SQL script to create the missing table:

```bash
# Connect to your PostgreSQL database and run:
psql -d your_database_name -f fix_missing_tables.sql
```

### Option 2: Manual Database Fix

If you prefer to run the SQL manually, connect to your PostgreSQL database and execute:

```sql
-- Create missing service_providers table
CREATE TABLE public.service_providers (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    code VARCHAR(100) UNIQUE,
    type VARCHAR(100) NOT NULL,
    address TEXT,
    phone VARCHAR(50),
    email VARCHAR(255),
    contact_person VARCHAR(255),
    status BOOLEAN DEFAULT TRUE,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Add foreign key to maintenance_schedules if needed
ALTER TABLE public.maintenance_schedules
ADD COLUMN IF NOT EXISTS service_provider_id BIGINT REFERENCES public.service_providers(id);

-- Create indexes
CREATE INDEX idx_service_providers_status ON public.service_providers(status);
CREATE INDEX idx_service_providers_type ON public.service_providers(type);
```

### Option 3: Laravel Migration Approach

If you want to use Laravel migrations (more proper approach):

1. Create a migration file:

```bash
php artisan make:migration create_service_providers_table
```

2. Edit the migration file to include the table structure
3. Run the migration:

```bash
php artisan migrate
```

## Database Connection Commands

To connect to your PostgreSQL database:

```bash
# If using default connection
psql -d transport_ims

# Or check your .env file for database credentials
cat .env | grep DB_
```

## Verification

After applying the fix, test that the application works by:

1. Visiting: http://localhost:8000/admin/vehicle-analytics
2. The page should load without errors
3. You should see service provider statistics

## Prevention

To prevent similar issues in the future:

1. Always run `php artisan migrate` when setting up a new environment
2. Keep database migrations in version control
3. Use Laravel's migration system instead of manual SQL files
4. Run `php artisan migrate:status` to check migration status

## Additional Notes

- The fix includes sample service provider data to get you started
- The `service_provider_id` column is added to `maintenance_schedules` table to support the model relationships
- All changes are backward compatible and won't affect existing data

## Next Steps

After fixing the database:

1. Test the Vehicle Analytics page
2. Consider adding proper data seeding for service providers
3. Review other parts of the application for similar missing table issues
4. Set up a proper migration workflow for future database changes
