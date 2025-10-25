@php
    $user = auth()->user();
    $hasSubscription = hasActiveSubscription();
    $isExpiring = isSubscriptionExpiring();
    $daysLeft = getSubscriptionDaysLeft();
@endphp

@if(in_array($user->role ?? '', ['admin', 'superadmin']))
    @if(!$hasSubscription)
        <!-- No Subscription - Disable Menu -->
        <div class="position-relative">
            <div class="subscription-overlay" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(255,255,255,0.8); z-index: 10; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                <div class="text-center">
                    <i class="fas fa-lock text-muted fa-2x mb-2"></i>
                    <div class="text-muted small">Berlangganan Diperlukan</div>
                </div>
            </div>
            {{ $slot }}
        </div>
    @elseif($isExpiring)
        <!-- Subscription Expiring - Show Warning -->
        <div class="position-relative">
            <div class="subscription-warning" style="position: absolute; top: -5px; right: -5px; z-index: 10;">
                <span class="badge bg-warning text-dark" title="Berlangganan akan berakhir dalam {{ $daysLeft }} hari">
                    <i class="fas fa-clock me-1"></i>
                    {{ $daysLeft }}d
                </span>
            </div>
            {{ $slot }}
        </div>
    @else
        <!-- Active Subscription - Normal Access -->
        {{ $slot }}
    @endif
@else
    <!-- Non-admin users - Normal Access -->
    {{ $slot }}
@endif

<style>
.subscription-overlay {
    backdrop-filter: blur(2px);
    -webkit-backdrop-filter: blur(2px);
}

.subscription-overlay:hover {
    background: rgba(255,255,255,0.9);
}

.subscription-warning .badge {
    font-size: 0.7rem;
    padding: 0.25rem 0.5rem;
    border-radius: 12px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
</style>
