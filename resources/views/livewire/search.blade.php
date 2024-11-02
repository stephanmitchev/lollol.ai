<div>
    <form wire:submit.prevent="search">
        <input type="text" wire:model="query" placeholder="Enter your search term">
        <button type="submit">Search</button>
    </form>

    @if($query)
        <h3>Search Results for "{{ $query }}"</h3>
        @if(count($results) > 0)
            <ul>
                @foreach($results as $result)
                    <li>{{ $result['title'] }}</li>
                @endforeach
            </ul>

           
        @else
            <p>No results found.</p>
        @endif
    @endif
</div>