<?php

use Livewire\Component;
use App\Models\RoomRate;
use App\Models\RoomType;

new class extends Component {

    public $rate_id = null;
    public $room_type_id = '';
    public $price_per_night = '';
    public $extra_guest_fee = 0;

    public $rates = [];
    public $roomTypes = [];

    public function mount()
    {
        $this->roomTypes = RoomType::all();
        $this->loadRates();
    }

    public function loadRates()
    {
        $this->rates = RoomRate::with('roomType')->latest()->get();
    }

    public function save()
    {
        $this->validate([
            'room_type_id' => 'required|exists:room_types,id',
            'price_per_night' => 'required|numeric|min:0',
            'extra_guest_fee' => 'nullable|numeric|min:0',
        ]);

        if ($this->rate_id) {
            RoomRate::findOrFail($this->rate_id)->update([
                'room_type_id' => $this->room_type_id,
                'price_per_night' => $this->price_per_night,
                'extra_guest_fee' => $this->extra_guest_fee ?: 0,
            ]);

            session()->flash('success', 'Room rate updated successfully.');
        } else {
            RoomRate::create([
                'room_type_id' => $this->room_type_id,
                'price_per_night' => $this->price_per_night,
                'extra_guest_fee' => $this->extra_guest_fee ?: 0,
            ]);

            session()->flash('success', 'Room rate created successfully.');
        }

        $this->resetForm();
        $this->roomTypes = RoomType::all();
        $this->loadRates();
    }

    public function edit($id)
    {
        $rate = RoomRate::findOrFail($id);

        $this->rate_id = $rate->id;
        $this->room_type_id = $rate->room_type_id;
        $this->price_per_night = $rate->price_per_night;
        $this->extra_guest_fee = $rate->extra_guest_fee;
    }

    public function delete($id)
    {
        RoomRate::findOrFail($id)->delete();
        $this->loadRates();
    }

    public function cancelEdit()
    {
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->rate_id = null;
        $this->room_type_id = '';
        $this->price_per_night = '';
        $this->extra_guest_fee = 0;
    }

}; ?>

<div class="max-w-7xl mx-auto z-10">
    <h1 class="text-3xl font-bold mb-6">Room Rates</h1>

    @if(session()->has('success'))
        <div class="bg-green-100 text-green-700 p-3 mb-4 rounded">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white border rounded-2xl shadow-sm p-6 mb-6">
        <h2 class="text-xl font-semibold mb-4">
            {{ $rate_id ? 'Edit Room Rate' : 'Add Room Rate' }}
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
                <label class="block mb-1 font-medium">Price Per Night</label>
                <input type="number" step="0.01" wire:model="price_per_night" class="w-full border rounded p-2">
                @error('price_per_night') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block mb-1 font-medium">Extra Guest Fee</label>
                <input type="number" step="0.01" wire:model="extra_guest_fee" class="w-full border rounded p-2">
                @error('extra_guest_fee') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="mt-4 flex gap-2">
            <button wire:click="save" class="bg-blue-600 text-white px-4 py-2 rounded">
                {{ $rate_id ? 'Update Rate' : 'Save Rate' }}
            </button>

            @if($rate_id)
                <button wire:click="cancelEdit" class="bg-gray-500 text-white px-4 py-2 rounded">
                    Cancel
                </button>
            @endif
        </div>
    </div>

    <div class="space-y-4">
        @forelse($rates as $rate)
            <div class="bg-white border rounded-2xl shadow-sm p-5">
                <p><strong>Room Type:</strong> {{ $rate->roomType->name }}</p>
                <p><strong>Price Per Night:</strong> ₱{{ number_format($rate->price_per_night, 2) }}</p>
                <p><strong>Extra Guest Fee:</strong> ₱{{ number_format($rate->extra_guest_fee, 2) }}</p>

                <div class="mt-3 flex gap-2">
                    <button wire:click="edit({{ $rate->id }})"
                            class="bg-yellow-500 text-white px-3 py-2 rounded">
                        Edit
                    </button>

                    <button wire:click="delete({{ $rate->id }})"
                            class="bg-red-600 text-white px-3 py-2 rounded">
                        Delete
                    </button>
                </div>
            </div>
        @empty
            <div class="bg-white border rounded-2xl p-5">
                No room rates found.
            </div>
        @endforelse
    </div>
</div>
