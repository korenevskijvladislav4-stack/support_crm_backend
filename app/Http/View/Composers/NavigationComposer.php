<?php

namespace App\Http\View\Composers;

use App\Models\Attempt;
use App\Models\User;
use Illuminate\View\View;

class NavigationComposer
{
    public function compose(View $view){
        $view->with('usersCount', User::all()->count());
        $view->with('currentUser', auth()->user());
        $view->with('attempts_count', Attempt::where('is_viewed', '0')->count());
    }
}
