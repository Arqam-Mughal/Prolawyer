<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'user' => new UserResource($this->whenLoaded('user')),
            'payment_method' => $this->payment_method,
            'order_id' => $this->order_id,
            'tracking_id' => $this->tracking_id,
            'payment_mode' => $this->payment_mode,
            'billing_info' => $this->billing_info,
            'trans_date' => $this->trans_date,
            'discount_value' => $this->discount_value,
            'stripe_charge_id' => $this->stripe_charge_id,
            'payment_intent' => $this->payment_intent,
            'payment_intent_client_secret' => $this->payment_intent_client_secret,
            'charged_amount' => $this->charged_amount,
            'description' => $this->description,
            'plan' => new RoleResource($this->whenLoaded('plan')),
            'plan_validity' => $this->plan_validity,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
