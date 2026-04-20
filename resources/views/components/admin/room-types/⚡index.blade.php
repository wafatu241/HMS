<?php

use Livewire\Component;
use App\Models\RoomType;

new class extends Component {

    public $room_type_id = null;
    public $name = '';
    public $description = '';
    public $capacity = 1;
    public $beds = 1;

    public $roomTypes = [];

    public function mount()
    {
        $this->loadRoomTypes();
    }

    public function loadRoomTypes()
    {
        $this->roomTypes = RoomType::latest()->get();
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'capacity' => 'required|integer|min:1',
            'beds' => 'required|integer|min:1',
        ]);

        if ($this->room_type_id) {
            RoomType::findOrFail($this->room_type_id)->update([
                'name' => $this->name,
                'description' => $this->description,
                'capacity' => $this->capacity,
                'beds' => $this->beds,
            ]);

            session()->flash('success', 'Room type updated successfully.');
        } else {
            RoomType::create([
                'name' => $this->name,
                'description' => $this->description,
                'capacity' => $this->capacity,
                'beds' => $this->beds,
            ]);

            session()->flash('success', 'Room type created successfully.');
        }

        $this->resetForm();
        $this->loadRoomTypes();
    }

    public function edit($id)
    {
        $type = RoomType::findOrFail($id);

        $this->room_type_id = $type->id;
        $this->name = $type->name;
        $this->description = $type->description;
        $this->capacity = $type->capacity;
        $this->beds = $type->beds;
    }

    public function delete($id)
    {
        RoomType::findOrFail($id)->delete();
        $this->loadRoomTypes();
    }

    public function cancelEdit()
    {
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->room_type_id = null;
        $this->name = '';
        $this->description = '';
        $this->capacity = 1;
        $this->beds = 1;
    }

}; ?>

<div class="max-w-5xl mx-auto p-2  fixed top-20 left-0 right-0 z-10">
    <h1 class="text-3xl font-bold mb-6">Room Types</h1>

    @if(session()->has('success'))
        <div class="bg-green-100 text-green-700 p-3 mb-4 rounded">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white border rounded-2xl shadow-sm p-6 mb-6">
        <h2 class="text-xl font-semibold mb-4">
            {{ $room_type_id ? 'Edit Room Type' : 'Add Room Type' }}
        </h2>

        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="block mb-1 font-medium">Name</label>
                <input type="text" wire:model="name" class="w-full border rounded p-2">
                @error('name') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block mb-1 font-medium">Description</label>
                <input type="text" wire:model="description" class="w-full border rounded p-2">
            </div>

            <div>
                <label class="block mb-1 font-medium">Capacity</label>
                <input type="number" wire:model="capacity" min="1" class="w-full border rounded p-2">
                @error('capacity') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block mb-1 font-medium">Beds</label>
                <input type="number" wire:model="beds" min="1" class="w-full border rounded p-2">
                @error('beds') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="mt-4 flex gap-2">
            <button wire:click="save" class="bg-blue-600 text-white px-4 py-2 rounded">
                {{ $room_type_id ? 'Update Room Type' : 'Save Room Type' }}
            </button>

            @if($room_type_id)
                <button wire:click="cancelEdit" class="bg-gray-500 text-white px-4 py-2 rounded">
                    Cancel
                </button>
            @endif
        </div>
    </div>

    <div class="space-y-4">
        @forelse($roomTypes as $type)
            <div class="bg-white border rounded-2xl shadow-sm p-5">
                <p><strong>Name:</strong> {{ $type->name }}</p>
                <p><strong>Description:</strong> {{ $type->description }}</p>
                <p><strong>Capacity:</strong> {{ $type->capacity }}</p>
                <p><strong>Beds:</strong> {{ $type->beds }}</p>

                <div class="mt-3 flex gap-2">
                    <button wire:click="edit({{ $type->id }})"
                            class="bg-yellow-500 text-white px-3 py-2 rounded">
                        Edit
                    </button>

                    <button wire:click="delete({{ $type->id }})"
                            class="bg-red-600 text-white px-3 py-2 rounded">
                        Delete
                    </button>
                </div>
            </div>
        @empty
            <div class="bg-white border rounded-2xl p-5">
                No room types found.
            </div>
        @endforelse
    </div>
</div>