<?php

namespace App\Livewire\Group;

use App\Models\Group;
use Livewire\Component;

class GroupsList extends Component
{
    public $users = [];
    public $groups = [];
    public $selectedGroup = null;

    public function mount(){
        $this->groups = Group::all();
    }
    public function selectGroup($groupId){
        $this->selectedGroup = Group::where('id', $groupId)->first();
        $this->users = $this->selectedGroup->users;
    }
    public function render()
    {
        return view('livewire.group.groups-list');
    }
}
