<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\Consultation;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('consultation.{consultationId}', function ($user, $consultationId) {
    $consultation = Consultation::find($consultationId);
    return $consultation && ($user->id == $consultation->doctor_id || $user->id == $consultation->patient_id);
});

Broadcast::channel('typing.consultation.{consultationId}', function ($user, $consultationId) {
    $consultation = Consultation::find($consultationId);
    return $consultation && ($user->id == $consultation->doctor_id || $user->id == $consultation->patient_id);
});
