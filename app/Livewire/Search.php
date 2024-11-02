<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;

class Search extends Component
{

    use WithPagination;

    public $query;

    protected $updatesQueryString = ['query'];

    public function updatedQuery()
    {
        $this->resetPage();
    }

    public function search() {
        $this->render();
    }

    public function render()
    {
        $results = [];

        if ($this->query) {
            //$results = \App\Models\YourSearchModel::where('title', 'like', '%' . $this->query . '%')->paginate(10);
            $results = [
                ['title' => 'result 1']
            ];
        }

        return view('livewire.search', [
            'results' => $results,
        ]);
    }
   
}
