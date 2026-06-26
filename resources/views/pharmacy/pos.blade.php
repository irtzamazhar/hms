@extends('layouts.hms')
@section('title','Pharmacy POS')
@section('breadcrumb')
    <span class="text-slate-400">Pharmacy</span> <span class="mx-1">/</span>
    <span class="font-medium text-slate-700 dark:text-slate-200">Point of Sale</span>
@endsection

@section('content')
<div x-data="pos()" class="grid grid-cols-1 lg:grid-cols-3 gap-4 h-full">

    {{-- Left: Medicine Search --}}
    <div class="lg:col-span-2 space-y-4">
        {{-- Search --}}
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-4">
            <input type="text" x-model="search" @input.debounce.300ms="searchMedicines()"
                   placeholder="Search medicines by name or barcode…"
                   class="field">
        </div>

        {{-- Search Results --}}
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden" x-show="results.length > 0">
            <div class="grid grid-cols-2 md:grid-cols-3 gap-0 divide-x divide-y divide-slate-100 dark:divide-slate-700 max-h-[60vh] overflow-y-auto">
                <template x-for="med in results" :key="med.id">
                    <button @click="addToCart(med)" type="button"
                            class="p-3 text-left hover:bg-primary-50 dark:hover:bg-primary-900/20 transition-colors"
                            :class="med.stock_quantity <= 0 ? 'opacity-50 cursor-not-allowed' : ''">
                        <p class="text-sm font-medium text-slate-700 dark:text-white truncate" x-text="med.name"></p>
                        <p class="text-xs text-slate-400" x-text="med.generic_name || ''"></p>
                        <div class="flex justify-between items-center mt-1">
                            <span class="text-sm font-bold text-primary-600" x-text="'₨ ' + med.sale_price"></span>
                            <span class="text-xs" :class="med.stock_quantity > 10 ? 'text-green-500' : 'text-amber-500'"
                                  x-text="'Stock: ' + med.stock_quantity"></span>
                        </div>
                    </button>
                </template>
            </div>
        </div>

        {{-- Common medicines grid (initial) --}}
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden" x-show="results.length === 0">
            <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-700">
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Recent / Common Medicines</p>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-0 divide-x divide-y divide-slate-100 dark:divide-slate-700 max-h-[60vh] overflow-y-auto">
                @foreach($medicines as $med)
                <button type="button"
                        x-on:click="addToCart({id:{{ $med->id }},name:{{ json_encode($med->name) }},generic_name:{{ json_encode($med->generic_name ?? '') }},sale_price:{{ $med->sale_price }},stock_quantity:{{ $med->stock_quantity }}})"
                        class="p-3 text-left hover:bg-primary-50 dark:hover:bg-primary-900/20 transition-colors {{ $med->stock_quantity <= 0 ? 'opacity-50 cursor-not-allowed' : '' }}">
                    <p class="text-sm font-medium text-slate-700 dark:text-white truncate">{{ $med->name }}</p>
                    <p class="text-xs text-slate-400">{{ $med->generic_name }}</p>
                    <div class="flex justify-between items-center mt-1">
                        <span class="text-sm font-bold text-primary-600">₨ {{ number_format($med->sale_price,0) }}</span>
                        <span class="text-xs {{ $med->stock_quantity > 10 ? 'text-green-500' : 'text-amber-500' }}">Stock: {{ $med->stock_quantity }}</span>
                    </div>
                </button>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Right: Cart --}}
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 flex flex-col">
        <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-700 flex justify-between items-center">
            <h2 class="font-bold text-slate-700 dark:text-white">Cart</h2>
            <button @click="cart = []" type="button" class="text-xs text-red-400 hover:text-red-600" x-show="cart.length">Clear</button>
        </div>

        {{-- Cart items --}}
        <div class="flex-1 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-700 max-h-[45vh]">
            <template x-if="cart.length === 0">
                <div class="px-4 py-8 text-center text-slate-400 text-sm">Cart is empty. Search and add medicines.</div>
            </template>
            <template x-for="(item, i) in cart" :key="i">
                <div class="px-4 py-3">
                    <div class="flex justify-between items-start">
                        <p class="text-sm font-medium text-slate-700 dark:text-white" x-text="item.name"></p>
                        <button @click="cart.splice(i,1)" type="button" class="text-slate-300 hover:text-red-500 ml-2">✕</button>
                    </div>
                    <div class="flex items-center gap-2 mt-1.5">
                        <button @click="item.qty > 1 ? item.qty-- : cart.splice(i,1)" type="button"
                                class="w-6 h-6 bg-slate-100 dark:bg-dark-700 text-slate-600 dark:text-slate-300 rounded font-bold text-sm hover:bg-slate-200 dark:hover:bg-dark-600 transition-colors flex items-center justify-center">−</button>
                        <input type="number" x-model.number="item.qty" min="1" :max="item.stock"
                               class="field w-14 text-center">
                        <button @click="item.qty < item.stock ? item.qty++ : null" type="button"
                                class="w-6 h-6 bg-slate-100 dark:bg-dark-700 text-slate-600 dark:text-slate-300 rounded font-bold text-sm hover:bg-slate-200 dark:hover:bg-dark-600 transition-colors flex items-center justify-center">+</button>
                        <span class="ml-auto text-sm font-semibold text-slate-700 dark:text-white" x-text="'₨ ' + (item.price * item.qty).toLocaleString()"></span>
                    </div>
                </div>
            </template>
        </div>

        {{-- Cart Footer --}}
        <div class="border-t border-slate-200 dark:border-slate-700 p-4 space-y-3">
            <form method="POST" action="{{ route('pharmacy.sale.store') }}" id="posForm">
                @csrf
                {{-- Hidden cart data --}}
                <template x-for="(item, i) in cart" :key="i">
                    <div>
                        <input type="hidden" :name="'items['+i+'][medicine_id]'" :value="item.id">
                        <input type="hidden" :name="'items['+i+'][quantity]'" :value="item.qty">
                        <input type="hidden" :name="'items['+i+'][unit_price]'" :value="item.price">
                    </div>
                </template>

                {{-- Patient (optional) --}}
                <div>
                    <label class="block text-xs font-medium text-slate-500 mb-1">Patient (optional)</label>
                    <select name="patient_id" class="field">
                        <option value="">Walk-in</option>
                        @foreach($patients as $p)
                            <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->mr_number }})</option>
                        @endforeach
                    </select>
                </div>

                {{-- Discount --}}
                <div class="flex gap-2">
                    <div class="flex-1">
                        <label class="block text-xs font-medium text-slate-500 mb-1">Discount (₨)</label>
                        <input type="number" name="discount_amount" x-model.number="discount" min="0" step="0.01"
                               class="field">
                    </div>
                    <div class="flex-1">
                        <label class="block text-xs font-medium text-slate-500 mb-1">Payment</label>
                        <select name="payment_method" class="field">
                            <option value="cash">Cash</option>
                            <option value="card">Card</option>
                            <option value="bank_transfer">Bank</option>
                        </select>
                    </div>
                </div>

                {{-- Totals --}}
                <div class="space-y-1 py-2 border-t border-slate-100 dark:border-slate-700">
                    <div class="flex justify-between text-sm"><span class="text-slate-400">Subtotal</span><span x-text="'₨ ' + subtotal().toLocaleString()"></span></div>
                    <div class="flex justify-between text-sm"><span class="text-slate-400">Discount</span><span x-text="'— ₨ ' + discount"></span></div>
                    <div class="flex justify-between font-bold text-base"><span>Total</span><span class="text-primary-600" x-text="'₨ ' + Math.max(0, subtotal() - discount).toLocaleString()"></span></div>
                </div>

                <input type="hidden" name="total_amount" :value="subtotal()">

                <button type="submit" @click="prepareSale($event)" :disabled="cart.length === 0"
                        class="w-full py-2.5 bg-primary-600 hover:bg-primary-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-bold text-sm rounded-lg">
                    Complete Sale
                </button>
            </form>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function pos() {
    return {
        search: '',
        results: [],
        cart: [],
        discount: 0,
        async searchMedicines() {
            if (this.search.length < 2) { this.results = []; return; }
            const resp = await fetch(`/pharmacy/search-medicines?q=${encodeURIComponent(this.search)}`);
            this.results = await resp.json();
        },
        addToCart(med) {
            if (med.stock_quantity <= 0) return;
            const existing = this.cart.find(i => i.id === med.id);
            if (existing) { if (existing.qty < med.stock_quantity) existing.qty++; }
            else this.cart.push({id: med.id, name: med.name, price: med.sale_price, qty: 1, stock: med.stock_quantity});
        },
        subtotal() {
            return this.cart.reduce((s, i) => s + i.price * i.qty, 0);
        },
        prepareSale(e) {
            if (this.cart.length === 0) { e.preventDefault(); return; }
        }
    }
}
</script>
@endpush
