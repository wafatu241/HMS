<?php

use Livewire\Component;
use App\Models\Room;
use App\Models\RoomType;
use Livewire\WithFileUploads;

new class extends Component {
    use WithFileUploads;

    public $room_id = null;
    public $room_type_id = '';
    public $room_number = '';
    public $floor = '';
    public $status = 'available';
    public $image = null;

    public $rooms = [];
    public $roomTypes = [];

    public function mount()
    {
        $this->roomTypes = RoomType::all();
        $this->loadRooms();
    }

    public function loadRooms()
    {
        $this->rooms = Room::with('roomType')->latest()->get();
    }

    public function save()
    {
        $rules = [
            'room_type_id' => 'required|exists:room_types,id',
            'floor' => 'nullable|string|max:255',
            'status' => 'required|in:available,occupied,maintenance',
            'image' => 'nullable|image|max:2048',
        ];

        if ($this->room_id) {
            $rules['room_number'] = 'required|string|max:255|unique:rooms,room_number,' . $this->room_id;
        } else {
            $rules['room_number'] = 'required|string|max:255|unique:rooms,room_number';
        }

        $this->validate($rules);

        if ($this->room_id) {
            $room = Room::findOrFail($this->room_id);

            $updateData = [
                'room_type_id' => $this->room_type_id,
                'room_number' => $this->room_number,
                'floor' => $this->floor,
                'status' => $this->status,
            ];

            if ($this->image) {
                $imagePath = $this->image->store('rooms', 'public');
                $updateData['image'] = $imagePath;
            }

            $room->update($updateData);

            session()->flash('success', 'Room updated successfully.');
        } else {
            $imagePath = null;

            if ($this->image) {
                $imagePath = $this->image->store('rooms', 'public');
            }

            Room::create([
                'room_type_id' => $this->room_type_id,
                'room_number' => $this->room_number,
                'floor' => $this->floor,
                'status' => $this->status,
                'image' => $imagePath,
            ]);

            session()->flash('success', 'Room created successfully.');
        }

        $this->resetForm();
        $this->roomTypes = RoomType::all();
        $this->loadRooms();
    }

    public function edit($id)
    {
        $room = Room::findOrFail($id);

        $this->room_id = $room->id;
        $this->room_type_id = $room->room_type_id;
        $this->room_number = $room->room_number;
        $this->floor = $room->floor;
        $this->status = $room->status;
        $this->image = null;
    }

    public function delete($id)
    {
        Room::findOrFail($id)->delete();
        $this->loadRooms();
    }

    public function cancelEdit()
    {
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->room_id = null;
        $this->room_type_id = '';
        $this->room_number = '';
        $this->floor = '';
        $this->status = 'available';
        $this->image = null;
    }

}; ?>

<div class="max-w-7xl mx-auto z-10">
    <h1 class="text-3xl font-bold mb-6">Rooms</h1>

    @if(session()->has('success'))
        <div class="bg-green-100 text-green-700 p-3 mb-4 rounded">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white border rounded-2xl shadow-sm p-6 mb-6">
        <h2 class="text-xl font-semibold mb-4">
            {{ $room_id ? 'Edit Room' : 'Add Room' }}
        </h2>

        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="block mb-1 font-medium">Room Type</label>
                <select wire:model="room_type_id" class="w-full border rounded p-2">
                    <option value="">Select Room Type</option>
                    @foreach($roomTypes as $type)
                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                    @endforeach
                </select>
                @error('room_type_id') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block mb-1 font-medium">Room Number</label>
                <input type="text" wire:model="room_number" class="w-full border rounded p-2">
                @error('room_number') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block mb-1 font-medium">Floor</label>
                <input type="text" wire:model="floor" class="w-full border rounded p-2">
                @error('floor') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block mb-1 font-medium">Status</label>
                <select wire:model="status" class="w-full border rounded p-2">
                    <option value="available">Available</option>
                    <option value="occupied">Occupied</option>
                    <option value="maintenance">Maintenance</option>
                </select>
                @error('status') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
            </div>

            <div class="md:col-span-2">
                <label class="block mb-1 font-medium">Upload Image</label>
                <input type="file" wire:model="image" class="w-full border rounded p-2">
                @error('image') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror

                @if ($image)
                    <div class="mt-3">
                        <img src="{{ $image->temporaryUrl() }}" alt="Preview" class="h-40 rounded-xl object-cover">
                    </div>
                @elseif ($room_id)
                    @php
                        $editingRoom = \App\Models\Room::find($room_id);
                    @endphp

                    @if($editingRoom && $editingRoom->image)
                        <div class="mt-3">
                            <img src="{{ asset('storage/' . $editingRoom->image) }}" alt="Current Image" class="h-40 rounded-xl object-cover">
                        </div>
                    @endif
                @endif
            </div>
        </div>

        <div class="mt-4 flex gap-2">
            <button wire:click="save" class="bg-blue-600 text-white px-4 py-2 rounded">
                {{ $room_id ? 'Update Room' : 'Save Room' }}
            </button>

            @if($room_id)
                <button wire:click="cancelEdit" class="bg-gray-500 text-white px-4 py-2 rounded">
                    Cancel
                </button>
            @endif
        </div>
    </div>

    <div class="space-y-4">
        @forelse($rooms as $room)
            <div class="bg-white border rounded-2xl shadow-sm p-5">
                @if($room->image)
                    <img
                        src="{{ asset('storage/' . $room->image) }}"
                        alt="Room Image"
                        class="w-full h-48 object-cover rounded-xl mb-4"
                    >
                @endif

                <p><strong>Room Number:</strong> {{ $room->room_number }}</p>
                <p><strong>Room Type:</strong> {{ $room->roomType->name }}</p>
                <p><strong>Floor:</strong> {{ $room->floor }}</p>
                <p><strong>Status:</strong> {{ ucfirst($room->status) }}</p>

                <div class="mt-3 flex gap-2">
                    <button wire:click="edit({{ $room->id }})"
                            class="bg-yellow-500 text-white px-3 py-2 rounded">
                        Edit
                    </button>

                    <button wire:click="delete({{ $room->id }})"
                            class="bg-red-600 text-white px-3 py-2 rounded">
                        Delete
                    </button>
                </div>
            </div>
        @empty
            <div class="bg-white border rounded-2xl p-5">
                No rooms found.
            </div>
        @endforelse
    </div>
</div>