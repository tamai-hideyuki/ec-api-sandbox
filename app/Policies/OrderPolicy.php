<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Order $order): bool
    {
        return $user->isAdmin() || $user->id === $order->user_id;
    }

    public function cancel(User $user, Order $order): bool
    {
        return $user->isAdmin() || $user->id === $order->user_id;
    }

    public function return(User $user, Order $order): bool
    {
        return $user->id === $order->user_id;
    }

    public function returnApprove(User $user): bool
    {
        return $user->isAdmin();
    }

    public function returnReject(User $user): bool
    {
        return $user->isAdmin();
    }
}
