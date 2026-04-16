@extends('layouts.app-master')

@push('css')
    <script src="{{ asset('assets/js/tailwindcss-cdn.js') }}"></script>
        <script>
            tailwind.config = {
                theme: {
                    extend: {
                        fontFamily: {
                            sans: ['DynamicAppFont', 'sans-serif'],
                        }
                    }
                }
            }
        </script>
        <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.css">
        <link href="{{ asset('assets/css/select2.min.css') }}" rel="stylesheet" />

        <style type="text/css">
            body { font-family: 'DynamicAppFont', sans-serif !important; }
            /* ── Map ── */
            #map {
                height: 650px;
                width: 100%;
            }

            /* ── Google Places autocomplete z-index fix ── */
            .pac-container {
                background-color: #fff;
                z-index: 2000;
                position: fixed;
                display: inline-block;
            }

            #pac-input {
                background-color: #fff;
                font-family: Roboto, sans-serif;
                font-size: 15px;
                font-weight: 300;
                margin-left: 12px;
                padding: 0 11px 0 13px;
                text-overflow: ellipsis;
                width: 400px;
                position: absolute;
                top: 11px;
                height: 40px;
                left: 188px;
            }

            /* ── Select2 overrides to match Tailwind input height/style ── */
            .select2-container {
                width: 100% !important;
                background: none;
                border: none;
            }

            .select2-container--classic .select2-selection--single {
                height: 42px !important;
                border: 1px solid #e5e7eb !important;
                border-radius: 0.375rem !important;
                background-color: #fff !important;
                box-shadow: none !important;
            }

            .select2-container--classic .select2-selection--single .select2-selection__rendered {
                line-height: 40px !important;
                padding-left: 12px !important;
                color: #374151 !important;
                font-size: 0.875rem !important;
            }

            .select2-container--classic .select2-selection--single .select2-selection__arrow {
                height: 40px !important;
                border-left: 1px solid #e5e7eb !important;
                background: transparent !important;
            }

            .select2-container--classic .select2-selection--single .select2-selection__clear {
                height: 38px !important;
                line-height: 38px !important;
            }

            .select2-container--classic .select2-selection--single:focus {
                border-color: #3b82f6 !important;
                outline: none !important;
            }

            .select2-container .select2-search--inline .select2-search__field {
                height: 20px !important;
            }

            .select2-dropdown {
                border: 1px solid #e5e7eb !important;
                border-radius: 0.375rem !important;
                z-index: 9999 !important;
            }

            /* ── Timepicker override ── */
            .ui-timepicker-wrapper {
                z-index: 9999 !important;
            }

            /* ── Custom map modal overlay ── */
            #locationURLMap {
                z-index: 1050;
            }

            .select2-selection--clearable > button > span{
                font-size: 20px;
                margin-right: 14px;
            }
        </style>
@endpush

@section('content')

    {{-- Page Header --}}
    <div class="px-6 pt-6 pb-2">
        <div class="flex items-center gap-3">
            <a href="{{ route('stores.index') }}" class="text-gray-400 hover:text-gray-600 transition-colors">
                <i class="bi bi-arrow-left text-xl"></i>
            </a>
            <div>
                <h2 class="text-2xl font-semibold text-gray-800">Add Location</h2>
                <p class="text-sm text-gray-400 mt-0.5">Locations &rsaquo; New Location</p>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('stores.store') }}" class="gift-submit-form">
        @csrf

        <div class="px-6 py-4 space-y-5">

            {{-- ── Section 1: Basic Info ── --}}
            <div class="bg-white rounded-xl border border-[#e5e7eb] shadow-sm pb-6">
                {{-- Section Header --}}
                <div class="flex items-start gap-3 px-6 py-4 border-b border-gray-100 mb-4">
                    <div class="mt-0.5 text-blue-500">
                        <i class="bi bi-building text-xl"></i>
                    </div>
                    <div class="flex-1">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <div>
                                <h3 class="text-base font-semibold text-gray-800">Location Details</h3>
                                <p class="text-sm text-gray-400">Type, category & identification</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Section Body --}}
                <div class="px-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">

                    {{-- Type --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5" for="store_type">
                            Type <span class="text-red-500">*</span>
                        </label>
                        <select name="store_type" id="store_type" class="w-full" required>
                            <option value=""></option>
                            @foreach ($storeTypes as $typeRow)
                                <option value="{{ $typeRow->id }}" @if(old('store_type') == $typeRow->id) selected @endif>
                                    {{ $typeRow->name }}
                                </option>
                            @endforeach
                        </select>
                        @if ($errors->has('store_type'))
                            <p class="text-red-500 text-xs mt-1.5">{{ $errors->first('store_type') }}</p>
                        @endif
                    </div>

                    {{-- Model Type --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5" for="model_type">
                            Model Type <span class="text-red-500">*</span>
                        </label>
                        <select name="model_type" id="model_type" class="w-full" required>
                            <option value=""></option>
                            @foreach ($modelTypes as $typeRow)
                                <option value="{{ $typeRow->id }}" @if(old('model_type') == $typeRow->id) selected @endif>
                                    {{ $typeRow->name }}
                                </option>
                            @endforeach
                        </select>
                        @if ($errors->has('model_type'))
                            <p class="text-red-500 text-xs mt-1.5">{{ $errors->first('model_type') }}</p>
                        @endif
                    </div>

                    {{-- Category --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5" for="store_category">
                            Category
                        </label>
                        <select name="store_category" id="store_category" class="w-full">
                            <option value=""></option>
                            @foreach ($storeCategories as $category_row)
                                <option value="{{ $category_row->id }}" @if(old('store_category') == $category_row->id) selected @endif>
                                    {{ $category_row->name }}
                                </option>
                            @endforeach
                        </select>
                        @if ($errors->has('store_category'))
                            <p class="text-red-500 text-xs mt-1.5">{{ $errors->first('store_category') }}</p>
                        @endif
                    </div>

                    {{-- Name --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                            Name <span class="text-red-500">*</span>
                        </label>
                        <input name="name" type="text"
                               class="w-full h-[42px] px-3 text-sm border border-[#e5e7eb] rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow"
                               value="{{ old('name') }}" placeholder="Location Name" required>
                        @if ($errors->has('name'))
                            <p class="text-red-500 text-xs mt-1.5">{{ $errors->first('name') }}</p>
                        @endif
                    </div>

                    {{-- Code --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                            Code <span class="text-red-500">*</span>
                        </label>
                        <input name="code" type="text"
                               class="w-full h-[42px] px-3 text-sm border border-[#e5e7eb] rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow"
                               value="{{ old('code') }}" placeholder="Location Code" required>
                        @if ($errors->has('code'))
                            <p class="text-red-500 text-xs mt-1.5">{{ $errors->first('code') }}</p>
                        @endif
                    </div>

                    {{-- Unique Code --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                            Unique Code <span class="text-red-500">*</span>
                        </label>
                        <input name="ucode" type="text"
                               class="w-full h-[42px] px-3 text-sm border border-[#e5e7eb] rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow"
                               value="{{ old('ucode') }}" placeholder="Unique Code" required>
                        @if ($errors->has('ucode'))
                            <p class="text-red-500 text-xs mt-1.5">{{ $errors->first('ucode') }}</p>
                        @endif
                    </div>

                    {{-- Number of Tables --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5" for="number_of_tables">
                            Number of Tables
                        </label>
                        <input name="number_of_tables" id="number_of_tables" type="number" min="0" max="9999"
                               class="w-full h-[42px] px-3 text-sm border border-[#e5e7eb] rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow"
                               value="{{ old('number_of_tables', 0) }}">
                        @if ($errors->has('number_of_tables'))
                            <p class="text-red-500 text-xs mt-1.5">{{ $errors->first('number_of_tables') }}</p>
                        @endif
                    </div>

                </div>
            </div>

            {{-- ── Section 2: Address ── --}}
            <div class="bg-white rounded-xl border border-[#e5e7eb] shadow-sm pb-6">
                <div class="flex items-start gap-3 px-6 py-4 border-b border-gray-100 mb-4">
                    <div class="mt-0.5 text-blue-500">
                        <i class="bi bi-geo-alt text-xl text-success"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-semibold text-gray-800">Address</h3>
                        <p class="text-sm text-gray-400">Physical location & map coordinates</p>
                    </div>
                </div>

                <div class="px-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">

                    {{-- Hidden location field --}}
                    <input name="location" id="location" type="hidden" value="{{ old('location') }}">

                    {{-- Address Line 1 --}}
                    <div class="md:col-span-2 lg:col-span-3">
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Address Line 1</label>
                        <input name="address1" type="text" id="searchTextField"
                               class="w-full h-[42px] px-3 text-sm border border-[#e5e7eb] rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow"
                               placeholder="Address Line 1" value="{{ old('address1') }}">
                        @if ($errors->has('address1'))
                            <p class="text-red-500 text-xs mt-1.5">{{ $errors->first('address1') }}</p>
                        @endif
                    </div>

                    {{-- Address Line 2 --}}
                    <div class="md:col-span-2 lg:col-span-3">
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Address Line 2</label>
                        <input name="address2" type="text"
                               class="w-full h-[42px] px-3 text-sm border border-[#e5e7eb] rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow"
                               placeholder="Address Line 2" value="{{ old('address2') }}">
                    </div>

                    {{-- Block --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Block</label>
                        <input name="block" type="text"
                               class="w-full h-[42px] px-3 text-sm border border-[#e5e7eb] rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow"
                               placeholder="Block" value="{{ old('block') }}">
                        @if ($errors->has('block'))
                            <p class="text-red-500 text-xs mt-1.5">{{ $errors->first('block') }}</p>
                        @endif
                    </div>

                    {{-- Street --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Street</label>
                        <input name="street" type="text"
                               class="w-full h-[42px] px-3 text-sm border border-[#e5e7eb] rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow"
                               placeholder="Street" value="{{ old('street') }}">
                        @if ($errors->has('street'))
                            <p class="text-red-500 text-xs mt-1.5">{{ $errors->first('street') }}</p>
                        @endif
                    </div>

                    {{-- Landmark --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Landmark</label>
                        <input name="landmark" type="text"
                               class="w-full h-[42px] px-3 text-sm border border-[#e5e7eb] rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow"
                               placeholder="Landmark" value="{{ old('landmark') }}">
                        @if ($errors->has('landmark'))
                            <p class="text-red-500 text-xs mt-1.5">{{ $errors->first('landmark') }}</p>
                        @endif
                    </div>

                    {{-- State --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5" for="state">
                            State <span class="text-red-500">*</span>
                        </label>
                        <select name="state" id="state" class="w-full" required></select>
                        @if ($errors->has('state'))
                            <p class="text-red-500 text-xs mt-1.5">{{ $errors->first('state') }}</p>
                        @endif
                    </div>

                    {{-- City --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5" for="city">
                            City <span class="text-red-500">*</span>
                        </label>
                        <select name="city" id="city" class="w-full" required></select>
                        @if ($errors->has('city'))
                            <p class="text-red-500 text-xs mt-1.5">{{ $errors->first('city') }}</p>
                        @endif
                    </div>

                    {{-- DoM --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5" for="dom_id">
                            DoM <span class="text-red-500">*</span>
                        </label>
                        <select name="dom_id" id="dom_id" class="w-full" required></select>
                        @if ($errors->has('dom_id'))
                            <p class="text-red-500 text-xs mt-1.5">{{ $errors->first('dom_id') }}</p>
                        @endif
                    </div>

                    {{-- Location URL --}}
                    <div class="md:col-span-2 lg:col-span-3">
                        <label class="block text-sm font-medium text-gray-700 mb-1.5" for="location_url">Location URL</label>
                        <div class="flex">
                            <input name="location_url" type="text" id="location_url"
                                   class="flex-1 h-[42px] px-3 text-sm border border-[#e5e7eb] rounded-l-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow"
                                   placeholder="Location URL" onkeydown="return false;"
                                   style="caret-color: transparent !important;" value="{{ old('location_url') }}" />
                            <button type="button"
                                    class="h-[42px] px-4 border border-l-0 border-[#e5e7eb] rounded-r-md bg-gray-50 hover:bg-gray-100 text-gray-600 transition-colors cursor-pointer"
                                    data-bs-toggle="modal" data-bs-target="#locationURLMap">
                                <i class="bi bi-pin-map"></i>
                            </button>
                        </div>
                        @if ($errors->has('location_url'))
                            <p class="text-red-500 text-xs mt-1.5">{{ $errors->first('location_url') }}</p>
                        @endif
                    </div>

                    {{-- Latitude --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5" for="map_latitude">Latitude</label>
                        <input name="map_latitude" type="text" id="map_latitude"
                               class="w-full h-[42px] px-3 text-sm border border-[#e5e7eb] rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow"
                               placeholder="Map Latitude"
                               style="caret-color: transparent !important;" value="{{ old('map_latitude') }}" />
                        @if ($errors->has('map_latitude'))
                            <p class="text-red-500 text-xs mt-1.5">{{ $errors->first('map_latitude') }}</p>
                        @endif
                    </div>

                    {{-- Longitude --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5" for="map_longitude">Longitude</label>
                        <input name="map_longitude" type="text" id="map_longitude"
                               class="w-full h-[42px] px-3 text-sm border border-[#e5e7eb] rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow"
                               placeholder="Map Longitude"
                               style="caret-color: transparent !important;" value="{{ old('map_longitude') }}" />
                        @if ($errors->has('map_longitude'))
                            <p class="text-red-500 text-xs mt-1.5">{{ $errors->first('map_longitude') }}</p>
                        @endif
                    </div>

                </div>
            </div>

            {{-- ── Section 3: Contact ── --}}
            <div class="bg-white rounded-xl border border-[#e5e7eb] shadow-sm pb-6">
                <div class="flex items-start gap-3 px-6 py-4 border-b border-gray-100 mb-4">
                    <div class="mt-0.5 text-blue-500">
                        <i class="bi bi-telephone text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-semibold text-gray-800">Contact</h3>
                        <p class="text-sm text-gray-400">Email, mobile & WhatsApp</p>
                    </div>
                </div>

                <div class="px-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">

                    {{-- Email --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5" for="email">Email</label>
                        <input name="email" type="email" id="email"
                               class="w-full h-[42px] px-3 text-sm border border-[#e5e7eb] rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow"
                               placeholder="Email" value="{{ old('email') }}">
                    </div>

                    {{-- Mobile --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Mobile Number</label>
                        <div class="flex">
                            <span class="inline-flex items-center h-[42px] px-3 text-sm border border-r-0 border-[#e5e7eb] rounded-l-md bg-gray-50 text-gray-600">
                                +91
                            </span>
                            <input name="mobile" type="hidden" id="mobile" value="{{ old('mobile') }}">
                            <input name="mobile_type" type="text" id="mobile_type"
                                   class="flex-1 h-[42px] px-3 text-sm border border-[#e5e7eb] rounded-r-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow"
                                   placeholder="Mobile" value="{{ old('mobile_type') }}">
                        </div>
                        @if ($errors->has('mobile_type'))
                            <p class="text-red-500 text-xs mt-1.5">{{ $errors->first('mobile_type') }}</p>
                        @endif
                    </div>

                    {{-- WhatsApp --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">WhatsApp Number</label>
                        <div class="flex">
                            <span class="inline-flex items-center h-[42px] px-3 text-sm border border-r-0 border-[#e5e7eb] rounded-l-md bg-gray-50 text-gray-600">
                                +91
                            </span>
                            <input name="whatsapp" type="hidden" id="whatsapp" value="{{ old('whatsapp') }}">
                            <input name="whatsapp_type" type="text" id="whatsapp_type"
                                   class="flex-1 h-[42px] px-3 text-sm border border-[#e5e7eb] rounded-r-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow"
                                   placeholder="WhatsApp" value="{{ old('whatsapp_type') }}">
                        </div>
                        @if ($errors->has('whatsapp_type'))
                            <p class="text-red-500 text-xs mt-1.5">{{ $errors->first('whatsapp_type') }}</p>
                        @endif
                    </div>

                </div>
            </div>

            {{-- ── Section 4: Operating Hours ── --}}
            <div class="bg-white rounded-xl border border-[#e5e7eb] shadow-sm pb-6">
                <div class="flex items-start gap-3 px-6 py-4 border-b border-gray-100 mb-4">
                    <div class="mt-0.5 text-blue-500">
                        <i class="bi bi-clock text-xl text-warning"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-semibold text-gray-800">Operating Hours</h3>
                        <p class="text-sm text-gray-400">Opening, closing & operations schedule</p>
                    </div>
                </div>

                <div class="px-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

                    {{-- Opening Time --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                            Opening Time <span class="text-red-500">*</span>
                        </label>
                        <input name="open_time" type="text"
                               class="timepicker w-full h-[42px] px-3 text-sm border border-[#e5e7eb] rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow"
                               value="{{ old('open_time') }}" placeholder="Opening Time" required>
                        @if ($errors->has('open_time'))
                            <p class="text-red-500 text-xs mt-1.5">{{ $errors->first('open_time') }}</p>
                        @endif
                    </div>

                    {{-- Closing Time --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                            Closing Time <span class="text-red-500">*</span>
                        </label>
                        <input name="close_time" type="text"
                               class="timepicker w-full h-[42px] px-3 text-sm border border-[#e5e7eb] rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow"
                               value="{{ old('close_time') }}" placeholder="Closing Time" required>
                        @if ($errors->has('close_time'))
                            <p class="text-red-500 text-xs mt-1.5">{{ $errors->first('close_time') }}</p>
                        @endif
                    </div>

                    {{-- Operation Start Time --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                            Operation Start <span class="text-red-500">*</span>
                        </label>
                        <input name="ops_start_time" type="text"
                               class="timepicker w-full h-[42px] px-3 text-sm border border-[#e5e7eb] rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow"
                               value="{{ old('ops_start_time') }}" placeholder="Operation Start Time" required>
                    </div>

                    {{-- Operation End Time --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                            Operation End <span class="text-red-500">*</span>
                        </label>
                        <input name="ops_end_time" type="text"
                               class="timepicker w-full h-[42px] px-3 text-sm border border-[#e5e7eb] rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow"
                               value="{{ old('ops_end_time') }}" placeholder="Operation End Time" required>
                    </div>

                </div>
            </div>

            {{-- ── Submit ── --}}
            <div class="flex justify-end pb-4">
                <button type="submit"
                        class="inline-flex items-center gap-2 px-6 py-2.5 bg-primary text-white text-sm font-medium rounded-lg transition-colors shadow-sm">
                    <i class="bi bi-plus-circle"></i>
                    Save Location
                </button>
            </div>

        </div>
    </form>


    {{-- ── Map Modal ── --}}
    {{-- Keep Bootstrap modal JS behavior; restyle shell with Tailwind --}}
    <div class="modal fade" id="locationURLMap" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
         aria-labelledby="locationURLMapLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width:1700px;">
            <div class="modal-content rounded-xl border-0 shadow-xl overflow-hidden">

                {{-- Modal Header --}}
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-white">
                    <h5 class="text-base font-semibold text-gray-800">Pin Location on Map</h5>
                    <button type="button"
                            class="text-gray-400 hover:text-gray-600 transition-colors"
                            data-bs-dismiss="modal" aria-label="Close">
                        <i class="bi bi-x-lg text-lg"></i>
                    </button>
                </div>

                {{-- Modal Body --}}
                <div class="bg-white p-0">
                    <div id="location_url_map">
                        <input id="pac-input" class="controls" type="text" placeholder="Search location..." />
                        <div id="map"></div>
                    </div>
                </div>

                {{-- Modal Footer --}}
                <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-100 bg-white">
                    <button type="button"
                            class="px-4 py-2 text-sm font-medium text-gray-600 border border-[#e5e7eb] rounded-lg hover:bg-gray-50 transition-colors"
                            data-bs-dismiss="modal">
                        Close
                    </button>
                    <button type="button" id="saveLocation"
                            class="px-4 py-2 text-sm font-medium text-white bg-primary rounded-lg transition-colors shadow-sm">
                        Save Location
                    </button>
                </div>

            </div>
        </div>
    </div>

@endsection

@push('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"
        integrity="sha512-pHVGpX7F/27yZ0ISY+VVjyULApbDlD0/X0rgGbTqCE7WFW5MezNTWG/dnhtbBuICzsd0WQPgpE4REBLv+UqChw=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.js"></script>
    <script src="https://polyfill.io/v3/polyfill.min.js?features=default"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAP_KEY') }}&libraries=places" async defer></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        (function() {
            'use strict';
            window.addEventListener('load', function() {
                var forms = document.getElementsByClassName('gift-submit-form');
                Array.prototype.filter.call(forms, function(form) {
                    form.addEventListener('submit', function(event) {
                        if (form.checkValidity() === false) {
                            event.preventDefault();
                            event.stopPropagation();
                            $(form).trigger('mdFormValidationErrors');
                        } else {
                            $(form).trigger('mdFormValidationSuccess');
                        }
                        form.classList.add('was-validated');
                    }, false);
                });
            }, false);
        })();

        jQuery(document).ready(function($) {

            // ── Select2 ──

            $('#state').select2({
                placeholder: 'Select State',
                allowClear: true,
                width: '100%',
                theme: 'classic',
                ajax: {
                    url: "{{ route('state-list') }}",
                    type: "POST",
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            searchQuery: params.term,
                            page: params.page || 1,
                            _token: "{{ csrf_token() }}"
                        };
                    },
                    processResults: function(data, params) {
                        params.page = params.page || 1;
                        return {
                            results: $.map(data.items, function(item) {
                                return { id: item.id, text: item.text };
                            }),
                            pagination: { more: data.pagination.more }
                        };
                    },
                    cache: true
                },
                templateResult: function(data) {
                    if (data.loading) return data.text;
                    return $('<span></span>').text(data.text);
                }
            }).on('change', function() {
                $('#city').val(null).trigger('change');
            });

            $('#city').select2({
                placeholder: 'Select City',
                allowClear: true,
                width: '100%',
                theme: 'classic',
                ajax: {
                    url: "{{ route('city-list') }}",
                    type: "POST",
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            searchQuery: params.term,
                            page: params.page || 1,
                            _token: "{{ csrf_token() }}",
                            state: function() { return $('#state option:selected').val(); }
                        };
                    },
                    processResults: function(data, params) {
                        params.page = params.page || 1;
                        return {
                            results: $.map(data.items, function(item) {
                                return { id: item.id, text: item.text };
                            }),
                            pagination: { more: data.pagination.more }
                        };
                    },
                    cache: true
                },
                templateResult: function(data) {
                    if (data.loading) return data.text;
                    return $('<span></span>').text(data.text);
                }
            });

            $('#dom_id').select2({
                placeholder: 'Select DOM',
                allowClear: true,
                width: '100%',
                theme: 'classic',
                ajax: {
                    url: "{{ route('users-list') }}",
                    type: "POST",
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            searchQuery: params.term,
                            page: params.page || 1,
                            _token: "{{ csrf_token() }}",
                            roles: "{{ implode(',', [Helper::$roles['divisional-operations-manager']]) }}"
                        };
                    },
                    processResults: function(data, params) {
                        params.page = params.page || 1;
                        return {
                            results: $.map(data.items, function(item) {
                                return { id: item.id, text: item.text };
                            }),
                            pagination: { more: data.pagination.more }
                        };
                    },
                    cache: true
                },
                templateResult: function(data) {
                    if (data.loading) return data.text;
                    return $('<span></span>').text(data.text);
                }
            });

            $('#store_type').select2({
                placeholder: 'Select Location Type',
                allowClear: true,
                width: '100%',
                theme: 'classic'
            });

            $('#model_type').select2({
                placeholder: 'Select Model Type',
                allowClear: true,
                width: '100%',
                theme: 'classic'
            });

            $('#store_category').select2({
                placeholder: 'Select Location Category',
                allowClear: true,
                width: '100%',
                theme: 'classic'
            });

            // ── Timepicker ──
            $('.timepicker').timepicker({
                timeFormat: 'h:mm p',
                interval: 15,
                dynamic: false,
                dropdown: true,
                scrollbar: true
            });

            // ── Phone masks ──
            $('#mobile_type,#whatsapp_type').mask('0#');

            $('#mobile_type').on('input', function(e) {
                $("#mobile").val("91" + e.target.value);
                if (e.target.value.length > 0 && e.target.value.length < 8) {
                    this.setCustomValidity('Please enter a valid mobile number!');
                } else {
                    this.setCustomValidity('');
                }
            });

            $('#whatsapp_type').on('input', function(e) {
                $("#whatsapp").val("91" + e.target.value);
                if (e.target.value.length > 0 && e.target.value.length < 8) {
                    this.setCustomValidity('Please enter a valid whatsapp number!');
                } else {
                    this.setCustomValidity('');
                }
            });
        });

        // ── Google Maps ──

        function initialize() {
            var input = document.getElementById('searchTextField');
            var autocomplete = new google.maps.places.Autocomplete(input);
            autocomplete.addListener('place_changed', function() {
                const place = autocomplete.getPlace();
                if (!place.geometry || !place.geometry.location) {
                    return;
                }
            });
        }

        let thisLat, thisLong, thisLatLongUrl, thePlaceName = null;

        function initAutocomplete() {
            const map = new google.maps.Map(document.getElementById("map"), {
                center: { lat: -33.8688, lng: 151.2195 },
                zoom: 13,
                mapTypeId: "roadmap",
            });

            const input = document.getElementById("pac-input");
            const searchBox = new google.maps.places.SearchBox(input);

            map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);
            map.addListener("bounds_changed", () => {
                searchBox.setBounds(map.getBounds());
            });

            let marker = null;

            function createMarker(position, title) {
                if (marker) {
                    marker.setMap(null);
                }

                const icon = {
                    url: "{{ url('assets/images/markers.png') }}",
                    scaledSize: new google.maps.Size(30, 30),
                };

                marker = new google.maps.Marker({
                    position,
                    map,
                    icon,
                    title,
                });

                return marker;
            }

            function logPlaceDetails(position, name) {
                const lat = position.lat();
                const lng = position.lng();
                const url = `https://www.google.com/maps/place/${lat},${lng}`;

                thisLat = lat;
                thisLong = lng;
                thisLatLongUrl = url;
                thePlaceName = name;
            }

            searchBox.addListener("places_changed", () => {
                const places = searchBox.getPlaces();

                if (places.length == 0) {
                    return;
                }

                const bounds = new google.maps.LatLngBounds();

                places.forEach((place) => {
                    if (!place.geometry || !place.geometry.location) {
                        return;
                    }

                    createMarker(place.geometry.location, place.name);
                    logPlaceDetails(place.geometry.location, place.name);

                    if (place.geometry.viewport) {
                        bounds.union(place.geometry.viewport);
                    } else {
                        bounds.extend(place.geometry.location);
                    }
                });
                map.fitBounds(bounds);
            });

            map.addListener('click', function(event) {
                createMarker(event.latLng, "Selected Location");
                logPlaceDetails(event.latLng, "Selected Location");
            });

            $('#locationURLMap').on('shown.bs.modal', function() {
                var center = map.getCenter();
                google.maps.event.trigger(map, 'resize');
                map.setCenter(center);
            });
        }

        window.initialize = initialize;
        window.initAutocomplete = initAutocomplete;

        $(document).on("click", "#saveLocation", function() {
            $("#location_url").val(thisLatLongUrl);
            $("#map_latitude").val(thisLat);
            $("#map_longitude").val(thisLong);
            $("#location").val(thePlaceName);
            $('#locationURLMap').modal('hide');
        });

    </script>
@endpush