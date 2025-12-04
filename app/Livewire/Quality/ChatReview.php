<?php

namespace App\Livewire\Quality;

use App\Models\Quality;
use App\Models\QualityReview;
use Livewire\Component;

class ChatReview extends Component
{
    public $slots = [];
    public $quality;
    public $selectedSlot = null;
    public $selectedReview = null;
    public $inputChatId;
    public $scores = [];
    public $comments = [];
    public $totalScore = 0;
    public $criteria = [];

    public function mount($id)
    {
        $this->quality = Quality::where('id', $id)->first();
        $this->criteria = $this->quality->user->team->criterias;
        $this->slots = QualityReview::where('quality_id', $id)->get();
    }

    public function createSlot()
    {
        $newSlot = $this->quality->reviews()->create([
            'quality_id' => $this->quality->id,
            'chat_id' => null,
        ]);
        foreach ($this->criteria as $criterion) {
            $newSlot->deductions()->create([
                'quality_review_id' => $newSlot->id,
                'quality_criteria_id' => $criterion->id,
            ]);
        }
        $this->selectSlot($newSlot->id);
        $this->slots->push($newSlot);
        session()->flash('message', 'Новый слот создан.');
    }

    public function selectSlot($slotNumber)
    {
        $this->selectedSlot = $slotNumber;
        $this->selectedReview = $this->quality->reviews()->where('id', $this->selectedSlot)->first();
        $this->totalScore = $this->selectedReview->total_score;
        $this->inputChatId = $this->selectedReview->review ?? '';
        $this->scores = $this->selectedReview->deductions()->pluck('points', 'quality_criteria_id')->toArray();
        $this->comments = $this->selectedReview->deductions()->pluck('comments', 'quality_criteria_id')->toArray();
    }

    public function save()
    {
        if (!$this->selectedSlot || !$this->inputChatId) {
            session()->flash('error', 'Пожалуйста, укажите Chat ID перед сохранением.');
            return;
        }
        $score = array_sum($this->scores);
        $this->totalScore = $score<100?100-$score:0;
        foreach ($this->criteria as $criterion) {
            $deduction = $this->selectedReview->deductions()
                ->where('quality_criteria_id', $criterion->id)
                ->first();
            if ($deduction) {
                $deduction->update([
                    'points' => $this->scores[$criterion->id] ?? 0,
                    'comments' => $this->comments[$criterion->id] ?? null,
                ]);
            }
        }
        $this->selectedReview->update([
            'review' => $this->inputChatId,
            'total_score'=>$this->totalScore
        ]);
        $this->quality->update([
            'average_quality'=>$this->quality->reviews()->avg('total_score'),
        ]);


        session()->flash('message', "Оценка сохранена для чата #{$this->inputChatId}");
    }

    public function render()
    {
        return view('livewire.qualities.chat-review');
    }
}
