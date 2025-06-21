-- Create missing service_providers table based on ServiceProvider model
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

-- Add service_provider_id column to maintenance_schedules table if it doesn't exist
DO $$ 
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.columns 
        WHERE table_name = 'maintenance_schedules' 
        AND column_name = 'service_provider_id'
    ) THEN
        ALTER TABLE public.maintenance_schedules 
        ADD COLUMN service_provider_id BIGINT REFERENCES public.service_providers(id);
    END IF;
END $$;

-- Create indexes for better performance
CREATE INDEX IF NOT EXISTS idx_service_providers_status ON public.service_providers(status);
CREATE INDEX IF NOT EXISTS idx_service_providers_type ON public.service_providers(type);
CREATE INDEX IF NOT EXISTS idx_maintenance_schedules_service_provider ON public.maintenance_schedules(service_provider_id);

-- Insert some sample service providers to get started
INSERT INTO public.service_providers (name, code, type, phone, email, contact_person, status) VALUES
('AutoCare Services', 'ACS001', 'General Maintenance', '+1-555-0101', 'info@autocare.com', 'John Smith', TRUE),
('QuickLube Express', 'QLE002', 'Oil Change', '+1-555-0102', 'service@quicklube.com', 'Sarah Johnson', TRUE),
('Tire Pros', 'TP003', 'Tire Service', '+1-555-0103', 'contact@tirepros.com', 'Mike Davis', TRUE),
('Engine Experts', 'EE004', 'Engine Repair', '+1-555-0104', 'help@engineexperts.com', 'Lisa Wilson', TRUE)
ON CONFLICT (code) DO NOTHING;
