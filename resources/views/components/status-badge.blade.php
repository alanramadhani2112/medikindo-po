@php
/**
 * Status Badge Component
 * Standardized color system for all status badges
 * 
 * Usage: <x-status-badge :status="$status" />
 * 
 * @param string $status - The status value
 * @param string $type - Optional: 'workflow', 'financial', 'active', 'risk' (default: 'workflow')
 */

$type = $type ?? 'workflow';
$status = strtolower($status ?? '');

// Workflow Status Colors
$workflowColors = [
    'draft' => 'badge-light-secondary',
    'pending' => 'badge-light-warning',
    'submitted' => 'badge-light-warning',
    'under_review' => 'badge-light-info',
    'approved' => 'badge-light-success',
    'rejected' => 'badge-light-danger',
    'sent' => 'badge-light-primary',
    
    'in_delivery' => 'badge-light-info',
    
    'completed' => 'badge-light-primary',
    'cancelled' => 'badge-light-secondary',
];

// Financial Status Colors
$financialColors = [
    'unpaid' => 'badge-light-warning',
    'paid' => 'badge-light-success',
    'overdue' => 'badge-light-danger',
    'partial' => 'badge-light-info',
    'payment_submitted' => 'badge-light-info',
    'issued' => 'badge-light-warning',
];

// Active/Inactive Colors
$activeColors = [
    'active' => 'badge-light-success',
    'inactive' => 'badge-light-secondary',
    'aktif' => 'badge-light-success',
    'nonaktif' => 'badge-light-secondary',
    'true' => 'badge-light-success',
    'false' => 'badge-light-secondary',
    '1' => 'badge-light-success',
    '0' => 'badge-light-secondary',
];

// Risk Status Colors (SOLID for high visibility)
$riskColors = [
    'high_risk' => 'badge-danger',
    'narcotic' => 'badge-danger',
    'narkotika' => 'badge-danger',
    'credit_hold' => 'badge-light-warning',
];

// Select color map based on type
$colorMap = match($type) {
    'financial' => $financialColors,
    'active' => $activeColors,
    'risk' => $riskColors,
    default => $workflowColors,
};

// Get color class
$colorClass = $colorMap[$status] ?? 'badge-light-secondary';

// Format display text
$displayText = strtoupper(str_replace('_', ' ', $status));
@endphp

<span class="badge {{ $colorClass }} fs-7 fw-semibold">{{ $displayText }}</span>
