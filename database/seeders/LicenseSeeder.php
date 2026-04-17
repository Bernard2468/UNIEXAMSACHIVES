<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\License;

class LicenseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $licenses = [
            ['name' => 'VEEAM', 'description' => 'Cloud Backup & Recovery Vault Subscription', 'is_active' => true],
            ['name' => 'KITEWORKS', 'description' => 'Secure File Exchange Pro Module', 'is_active' => true],
            ['name' => 'IRONNET', 'description' => 'IronDOM Secure Archive Protection License', 'is_active' => true],
            ['name' => 'KLIPFOLIO', 'description' => 'Analytics Dashboard & UI Theme Pack', 'is_active' => true],
            ['name' => 'IRONNET', 'description' => 'IronDOM Network Security Insurance License', 'is_active' => true],
            ['name' => 'KITEWORKS', 'description' => 'Multi-Factor Authentication (MFA) Security Pack', 'is_active' => true],
            ['name' => 'KOOFR', 'description' => 'Deployment & Scaling Toolkit', 'is_active' => true],
            ['name' => 'SUMTOTAL', 'description' => 'Training & Documentation License', 'is_active' => true],
            ['name' => 'SUMTOTAL', 'description' => 'Annual Maintenance & Security Patch Subscription', 'is_active' => true],
            ['name' => 'KOOFR', 'description' => 'API Integration Pack (Registry, HR, Library)', 'is_active' => true],
            ['name' => 'KOOFR', 'description' => 'Digitization & Legacy Import Tool (OCR Edition)', 'is_active' => true],
            ['name' => 'DEEPVA', 'description' => 'Archive Repository Engine (Pro Edition)', 'is_active' => true],
            ['name' => 'DEEPVA', 'description' => 'Security Hardening Suite (Premium Edition)', 'is_active' => true],
            ['name' => 'KLEARSTACK', 'description' => 'Advanced OCR Search Engine License', 'is_active' => true],
            ['name' => 'KLEARSTACK', 'description' => 'Disaster Recovery Vault Subscription', 'is_active' => true],
            ['name' => 'DEEPVA', 'description' => 'File Integrity Verification Toolkit', 'is_active' => true],
            ['name' => 'DEEPVA', 'description' => 'AI-Metadata Indexing Module', 'is_active' => true],
            ['name' => 'KLEARSTACK', 'description' => 'Archive Access Control Plugin (Enterprise)', 'is_active' => true],
            ['name' => 'SOLARWINDS', 'description' => 'RBAC User Access Control Suite (Enterprise License)', 'is_active' => true],
            ['name' => 'COMMVAULT', 'description' => 'API Gateway & Integration Firewall License', 'is_active' => true],
            ['name' => 'AMPERE', 'description' => 'Pen-Test & Security Hardening Suite', 'is_active' => true],
            ['name' => 'AMPERE', 'description' => 'Digital Preservation Framework License', 'is_active' => true],
            ['name' => 'AUDITBOARD', 'description' => 'Audit & Regulatory Compliance Monitoring Tool', 'is_active' => true],
            ['name' => 'TALEND', 'description' => 'Premium Admin Dashboard Template Pack', 'is_active' => true],
            ['name' => 'AMPERE', 'description' => 'Performance Optimization Toolkit', 'is_active' => true],
            ['name' => 'S.E.A', 'description' => 'Support & System Update Subscription (12 Months)', 'is_active' => true],
            ['name' => 'HYPACK XYLEM', 'description' => 'Communication Workflow Designer License', 'is_active' => true],
            ['name' => 'HYPACK XYLEM', 'description' => 'Annual Support, Updates & Patch Subscription', 'is_active' => true],
            ['name' => 'MULESOFT', 'description' => 'IronDOM Cybersecurity Shield (Enterprise Protection License)', 'is_active' => true],
            ['name' => 'MADCAP', 'description' => 'Technical Documentation & Training Course License', 'is_active' => true],
            ['name' => 'GLOBAL SOFT SYSTEMS', 'description' => 'Enterprise Workflow Automation Engine License (Annual)', 'is_active' => true],
            ['name' => 'DOCWARE', 'description' => 'Document Management Core Module (Perpetual License)', 'is_active' => true],
            ['name' => 'SMSGLOBAL', 'description' => 'Notification Hub Pro (Email + SMS Gateway Subscription)', 'is_active' => true],
            ['name' => 'WISE SYSTEMS', 'description' => 'AI-Assisted Routing Engine Add-On', 'is_active' => true],
            ['name' => 'S.E.A', 'description' => 'Deployment & Scaling Toolkit License', 'is_active' => true],
            ['name' => 'MULESOFT', 'description' => 'Deployment & Optimization Toolkit', 'is_active' => true],
            ['name' => 'MULESOFT', 'description' => 'System Integration API Pack', 'is_active' => true],
            ['name' => 'SMSGLOBAL', 'description' => 'Enterprise Encryption Suite', 'is_active' => true],
            ['name' => 'PIXINVENT', 'description' => 'Premium UI Dashboard Theme License Software', 'is_active' => true],
            ['name' => 'GLOBALSIGN', 'description' => 'Digital Signature & Verification Toolkit (HSM-Backend)', 'is_active' => true],
        ];

        foreach ($licenses as $license) {
            License::create($license);
        }
    }
}
