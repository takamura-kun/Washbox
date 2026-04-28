@extends('admin.layouts.app')

@section('title', 'Create New Branch')
@section('page-title', 'Create New Branch')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('admin.branches.index') }}" class="me-2 text-dark"><i class="bi bi-arrow-left"></i></a>
        <h5 class="fw-bold mb-0">Add New Branch</h5>
    </div>

    @if ($errors->any())
    <div class="alert alert-danger mb-4">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('admin.branches.store') }}" class="card shadow-sm p-4" enctype="multipart/form-data">
        @csrf

        <div class="row">

            {{-- ═══════════════════════════════════════ --}}
            {{-- LEFT COLUMN                            --}}
            {{-- ═══════════════════════════════════════ --}}
            <div class="col-lg-8">
                <h5 class="fw-bold mb-3"><i class="bi bi-building me-2"></i>Branch Information</h5>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Branch Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm @error('name') is-invalid @enderror"
                               name="name" value="{{ old('name') }}" required placeholder="e.g., WashBox Sibulan">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Branch Code <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm @error('code') is-invalid @enderror"
                               name="code" value="{{ old('code') }}" required maxlength="10" placeholder="e.g., SBL, DGT, BAI">
                        <small class="text-muted">Unique 3–10 letter code</small>
                        @error('code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Address <span class="text-danger">*</span></label>
                    <textarea class="form-control form-control-sm @error('address') is-invalid @enderror"
                              name="address" rows="2" required
                              placeholder="Full street address (e.g., Purok 3, Diversion Road, Magatas)">{{ old('address') }}</textarea>
                    @error('address')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Barangay/District</label>
                        <input type="text" class="form-control form-control-sm @error('barangay') is-invalid @enderror"
                               name="barangay" value="{{ old('barangay') }}" placeholder="e.g., Magatas">
                        @error('barangay')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">City/Municipality <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm @error('city') is-invalid @enderror"
                               name="city" value="{{ old('city') }}" required placeholder="e.g., Sibulan">
                        @error('city')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Province <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm @error('province') is-invalid @enderror"
                               name="province" value="{{ old('province', $defaultProvince ?? 'Negros Oriental') }}"
                               required placeholder="e.g., Negros Oriental">
                        @error('province')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Zip Code</label>
                        <input type="text" class="form-control form-control-sm @error('zip_code') is-invalid @enderror"
                               name="zip_code" value="{{ old('zip_code') }}" placeholder="e.g., 6200" maxlength="4">
                        @error('zip_code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Phone Number <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm @error('phone') is-invalid @enderror"
                               name="phone" value="{{ old('phone') }}" required placeholder="e.g., 0917 123 4567">
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Email Address</label>
                        <input type="email" class="form-control form-control-sm @error('email') is-invalid @enderror"
                               name="email" value="{{ old('email') }}" placeholder="e.g., branch@washbox.com">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Branch Manager</label>
                        <input type="text" class="form-control form-control-sm @error('manager') is-invalid @enderror"
                               name="manager" value="{{ old('manager') }}" placeholder="Name of branch manager">
                        @error('manager')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <h5 class="fw-bold mb-3 mt-4"><i class="bi bi-shield-lock me-2"></i>Branch Login Credentials</h5>
                <div class="alert alert-info py-2 mb-3">
                    <i class="bi bi-info-circle me-1"></i>
                    <small>These credentials will be used by all staff at this branch to access the system.</small>
                </div>

                <div class="row mb-4">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Username <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm @error('username') is-invalid @enderror"
                               name="username" value="{{ old('username') }}" required placeholder="e.g., sbl, dgt, bai">
                        <small class="text-muted">Lowercase, 3-10 characters</small>
                        @error('username')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control form-control-sm @error('password') is-invalid @enderror"
                               name="password" required placeholder="Minimum 8 characters">
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Confirm Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control form-control-sm"
                               name="password_confirmation" required placeholder="Re-enter password">
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Status</label>
                        <select class="form-select form-select-sm @error('is_active') is-invalid @enderror" name="is_active">
                            <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ old('is_active', '1') == '0' ? 'selected' : '' }}>Inactive</option>
                        </select>
                        <small class="text-muted">Inactive branches won't appear in mobile app</small>
                        @error('is_active')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- ═══ MAP CARD ═══ --}}
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h6 class="fw-bold mb-0"><i class="bi bi-geo-alt me-2"></i>Branch Location</h6>
                        <small class="text-muted">
                            Search by address, Plus Code (e.g. <code>972F+JH4, Sibulan</code>), or click the map to pin
                        </small>
                    </div>
                    <div class="card-body p-0">

                        {{-- Search bar --}}
                        <div class="p-3 border-bottom bg-light">
                            <div class="input-group">
                                <input type="text" id="map-search-input" class="form-control"
                                       placeholder="e.g., Purok 3, Diversion Road, Magatas, Sibulan, 6200 Negros Oriental">
                                <button type="button" class="btn btn-primary" id="btn-search-map">
                                    <i class="bi bi-search me-1"></i> Search
                                </button>
                                <button type="button" class="btn btn-success" id="btn-locate-address">
                                    <i class="bi bi-crosshair me-1"></i> Locate from Form
                                </button>
                            </div>
                            <div id="geocode-status" class="small mt-2" style="display:none;"></div>

                            <div class="mt-2">
                                <small class="text-muted">
                                    <i class="bi bi-info-circle me-1"></i>
                                    <strong>Philippine addresses supported:</strong> Purok, Street, Barangay, City/Municipality, Province, Zip Code<br>
                                    <i class="bi bi-plus-circle me-1"></i>
                                    Supports: regular addresses, Google Plus Codes (<code>972F+JH4, Sibulan</code>), and landmark names
                                </small>
                            </div>
                        </div>

                        {{-- Map --}}
                        <div id="branch-map" style="height: 350px; width: 100%;"></div>

                        {{-- Coordinate display --}}
                        <div class="p-3 bg-white border-top">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <span class="badge bg-primary-subtle text-primary px-3 py-2" id="coord-display">
                                        <i class="bi bi-pin-map me-1"></i> No location set
                                    </span>
                                </div>
                                <div class="col-auto ms-auto">
                                    <button type="button" class="btn btn-sm btn-outline-danger"
                                            id="btn-clear-location" style="display:none;">
                                        <i class="bi bi-x-circle me-1"></i> Clear
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Hidden inputs --}}
                        <input type="hidden" name="latitude"  id="latitude"  value="{{ old('latitude') }}">
                        <input type="hidden" name="longitude" id="longitude" value="{{ old('longitude') }}">
                    </div>
                </div>

            </div>{{-- end col-lg-8 --}}

            {{-- ═══════════════════════════════════════ --}}
            {{-- RIGHT SIDEBAR                          --}}
            {{-- ═══════════════════════════════════════ --}}
            <div class="col-lg-4">

                {{-- Quick Tips --}}
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h6 class="fw-bold mb-0"><i class="bi bi-info-circle me-2"></i>Quick Tips</h6>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info py-2">
                            <h6 class="small mb-2"><i class="bi bi-lightbulb me-1"></i>Best Practices:</h6>
                            <ul class="mb-0 small">
                                <li>Use unique branch codes (SBL, DGT, BAI)</li>
                                <li>Ensure phone numbers are correct for customer contact</li>
                                <li>Use the map to pin your branch location</li>
                            </ul>
                        </div>

                        <div class="alert alert-success py-2">
                            <h6 class="small mb-2"><i class="bi bi-geo-alt me-1"></i>Philippine Address Format:</h6>
                            <ul class="mb-0 small">
                                <li><strong>Example:</strong> Purok 3, Diversion Road, Magatas, Sibulan, 6200 Negros Oriental</li>
                                <li>Purok / Sitio / Subdivision</li>
                                <li>Street / Road name</li>
                                <li>Barangay → City/Municipality → Zip + Province</li>
                            </ul>
                        </div>

                        <div class="alert alert-warning py-2">
                            <h6 class="small mb-2"><i class="bi bi-exclamation-triangle me-1"></i>Important:</h6>
                            <ul class="mb-0 small">
                                <li>Inactive branches won't appear in mobile app</li>
                                <li>Branch code cannot be changed after creation</li>
                                <li>Click the map to set exact location if search is not precise</li>
                            </ul>
                        </div>
                    </div>
                </div>

                {{-- ═══ GCASH SETTINGS CARD ═══ --}}
                <div class="card">
                    <div class="card-header bg-light d-flex align-items-center justify-content-between">
                        <h6 class="fw-bold mb-0">
                            <i class="bi bi-qr-code-scan text-primary me-2"></i>GCash Payment Settings
                        </h6>
                        <span class="badge bg-primary-subtle text-primary small px-2">Per Branch</span>
                    </div>
                    <div class="card-body">

                        <p class="text-muted small mb-3">
                            <i class="bi bi-info-circle me-1"></i>
                            Used for mobile app, walk-in counter, and pickup/delivery payments.
                        </p>

                        {{-- GCash Number --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                GCash Number <span class="text-danger">*</span>
                            </label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-primary-subtle border-0">
                                    <i class="bi bi-phone text-primary"></i>
                                </span>
                                <input type="text"
                                       class="form-control form-control-sm @error('gcash_number') is-invalid @enderror"
                                       name="gcash_number"
                                       id="gcash_number"
                                       value="{{ old('gcash_number') }}"
                                       placeholder="e.g., 0917 123 4567"
                                       maxlength="13">
                                @error('gcash_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <small class="text-muted">Displayed to customers during payment.</small>
                        </div>

                        {{-- Account Name --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Account Name <span class="text-danger">*</span>
                            </label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-primary-subtle border-0">
                                    <i class="bi bi-person text-primary"></i>
                                </span>
                                <input type="text"
                                       class="form-control form-control-sm @error('gcash_name') is-invalid @enderror"
                                       name="gcash_name"
                                       id="gcash_name"
                                       value="{{ old('gcash_name') }}"
                                       placeholder="e.g., Juan Dela Cruz">
                                @error('gcash_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <small class="text-muted">Name shown on GCash confirmation screen.</small>
                        </div>

                        {{-- QR Code Upload --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">GCash QR Code</label>

                            <div id="qr-dropzone"
                                 class="border border-2 rounded-3 text-center p-3 qr-dropzone-box">
                                <input type="file"
                                       name="gcash_qr_image"
                                       id="gcash_qr"
                                       accept="image/png,image/jpeg,image/jpg"
                                       class="d-none @error('gcash_qr_image') is-invalid @enderror">

                                {{-- Placeholder state --}}
                                <div id="qr-placeholder">
                                    <i class="bi bi-qr-code display-6 text-muted"></i>
                                    <p class="small text-muted mt-2 mb-0">
                                        Click or drag & drop QR image<br>
                                        <span style="font-size:0.75rem;">PNG or JPG — max 2MB</span>
                                    </p>
                                </div>

                                {{-- Preview state --}}
                                <div id="qr-preview-wrap" style="display:none;">
                                    <img id="qr-preview-img" src="#" alt="QR Preview"
                                         class="img-fluid rounded" style="max-height:150px; object-fit:contain;">
                                    <div class="mt-2">
                                        <button type="button" class="btn btn-sm btn-outline-danger" id="btn-remove-qr">
                                            <i class="bi bi-trash me-1"></i> Remove
                                        </button>
                                    </div>
                                </div>
                            </div>

                            @error('gcash_qr_image')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Shown on the mobile app payment screen.</small>
                        </div>

                        {{-- GCash live summary --}}
                        <div class="p-2 rounded-3 border gcash-summary-box mt-3" id="gcash-summary" style="display:none;">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-check-circle-fill text-success fs-5"></i>
                                <div class="small lh-sm">
                                    <div class="fw-semibold" id="gcash-summary-name">—</div>
                                    <div class="text-muted" id="gcash-summary-number">—</div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>{{-- end GCash card --}}

                {{-- Action Buttons --}}
                <div class="d-grid gap-2 mt-4">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="bi bi-plus-circle me-2"></i>Create Branch
                    </button>
                    <a href="{{ route('admin.branches.index') }}" class="btn btn-light border">
                        <i class="bi bi-x-circle me-2"></i>Cancel
                    </a>
                </div>

            </div>{{-- end col-lg-4 --}}

        </div>{{-- end row --}}
    </form>
</div>
@endsection

@push('scripts')
{{-- Leaflet --}}
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
{{-- Open Location Code (Plus Code decoder) - Browser compatible version --}}
<script>
// Simple Plus Code decoder fallback if the library fails
window.OpenLocationCode = window.OpenLocationCode || {
    isValid: function(code) {
        // Basic validation for Plus Code format
        return /^[23456789CFGHJMPQRVWX]{4,8}\+[23456789CFGHJMPQRVWX]{2,}$/i.test(code);
    },
    decode: function(code) {
        // Fallback - return approximate coordinates for Philippines
        return {
            latitudeCenter: 9.3068,
            longitudeCenter: 123.3054
        };
    },
    recoverNearest: function(shortCode, refLat, refLng) {
        // Simple recovery - just return the short code as is
        return shortCode;
    }
};
</script>
<script src="https://cdn.jsdelivr.net/npm/open-location-code@1.0.3/openlocationcode.min.js" onerror="console.log('Plus Code library failed to load, using fallback')"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    // ═══════════════════════════════════════
    // FIELD REFERENCES
    // ═══════════════════════════════════════
    const nameInput     = document.querySelector('input[name="name"]');
    const codeInput     = document.querySelector('input[name="code"]');
    const addressInput  = document.querySelector('textarea[name="address"]');
    const phoneInput    = document.querySelector('input[name="phone"]');
    const barangayInput = document.querySelector('input[name="barangay"]');
    const cityInput     = document.querySelector('input[name="city"]');
    const provinceInput = document.querySelector('input[name="province"]');
    const zipInput      = document.querySelector('input[name="zip_code"]');

    // ═══════════════════════════════════════
    // PHONE FORMAT  (branch phone)
    // ═══════════════════════════════════════
    function formatPhone(input) {
        input.addEventListener('input', function (e) {
            let v = e.target.value.replace(/\D/g, '').slice(0, 11);
            if (v.length > 7)      v = v.slice(0, 4) + ' ' + v.slice(4, 7) + ' ' + v.slice(7);
            else if (v.length > 4) v = v.slice(0, 4) + ' ' + v.slice(4);
            e.target.value = v;
        });
    }
    formatPhone(phoneInput);

    // ═══════════════════════════════════════
    // MAP SETUP
    // ═══════════════════════════════════════
    const latInput     = document.getElementById('latitude');
    const lngInput     = document.getElementById('longitude');
    const coordDisplay = document.getElementById('coord-display');
    const statusDiv    = document.getElementById('geocode-status');
    const clearBtn     = document.getElementById('btn-clear-location');
    const searchInput  = document.getElementById('map-search-input');

    const DEFAULT_LAT = 9.3068;
    const DEFAULT_LNG = 123.3054;

    const map = L.map('branch-map').setView([DEFAULT_LAT, DEFAULT_LNG], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap',
        maxZoom: 19
    }).addTo(map);

    let marker = null;

    function setMarker(lat, lng, flyTo = true) {
        lat = parseFloat(lat);
        lng = parseFloat(lng);
        if (isNaN(lat) || isNaN(lng)) return;

        if (marker) {
            marker.setLatLng([lat, lng]);
        } else {
            marker = L.marker([lat, lng], { draggable: true }).addTo(map);
            marker.on('dragend', function (e) {
                const pos = e.target.getLatLng();
                updateCoordinates(pos.lat, pos.lng);
            });
        }

        if (flyTo) map.flyTo([lat, lng], 17, { duration: 1.2 });
        updateCoordinates(lat, lng);
    }

    function updateCoordinates(lat, lng) {
        latInput.value  = parseFloat(lat).toFixed(6);
        lngInput.value  = parseFloat(lng).toFixed(6);
        coordDisplay.innerHTML = `<i class="bi bi-pin-map-fill me-1"></i> ${parseFloat(lat).toFixed(6)}, ${parseFloat(lng).toFixed(6)}`;
        coordDisplay.className = 'badge bg-success-subtle text-success px-3 py-2';
        clearBtn.style.display = 'inline-block';
    }

    function showStatus(message, type) {
        statusDiv.style.display = 'block';
        const icon = type === 'loading' ? 'arrow-repeat' :
                     type === 'success' ? 'check-circle' : 'exclamation-circle';
        statusDiv.className = `small mt-2 geocode-${type}`;
        statusDiv.innerHTML = `<i class="bi bi-${icon} me-1${type === 'loading' ? ' spinner-rotate' : ''}"></i> ${message}`;
        if (type !== 'loading') setTimeout(() => { statusDiv.style.display = 'none'; }, 6000);
    }

    map.on('click', function (e) {
        setMarker(e.latlng.lat, e.latlng.lng, false);
        showStatus('Location set by clicking the map. Drag the marker to fine-tune.', 'success');
    });

    // ═══════════════════════════════════════
    // NOMINATIM GEOCODER
    // ═══════════════════════════════════════
    async function nominatimGeocode(query) {
        try {
            let searchQuery = query;
            if (!/philippines|pilipinas/i.test(searchQuery)) {
                searchQuery += ', Philippines';
            }
            searchQuery = searchQuery.replace(/\s+/g, ' ').trim();

            const url = `https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(searchQuery)}&format=json&limit=1&countrycodes=ph&addressdetails=1`;
            const resp = await fetch(url, {
                headers: {
                    'User-Agent': 'WashBox Laundry Management System',
                    'Accept-Language': 'en-US,en;q=0.9'
                }
            });

            if (!resp.ok) throw new Error(`HTTP error! status: ${resp.status}`);

            const data = await resp.json();
            if (!data || data.length === 0) return await trySimplifiedGeocode(query);

            return {
                lat: parseFloat(data[0].lat),
                lng: parseFloat(data[0].lon),
                display: data[0].display_name
            };
        } catch (error) {
            console.error('Geocoding error:', error);
            return null;
        }
    }

    async function trySimplifiedGeocode(originalQuery) {
        let simplified = originalQuery.replace(/\b\d{4}\b/g, '').trim();
        const parts = simplified.split(',').map(p => p.trim());
        const attempts = [
            simplified,
            parts.slice(-2).join(', '),
            parts[parts.length - 1],
            parts[parts.length - 2]
        ];

        for (const attempt of attempts) {
            if (!attempt) continue;
            const url = `https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(attempt + ', Philippines')}&format=json&limit=1&countrycodes=ph`;
            const resp = await fetch(url, { headers: { 'User-Agent': 'WashBox Laundry Management System' } });
            const data = await resp.json();
            if (data && data.length > 0) {
                return {
                    lat: parseFloat(data[0].lat),
                    lng: parseFloat(data[0].lon),
                    display: data[0].display_name,
                    note: 'Approximate location (based on city/province)'
                };
            }
        }
        return null;
    }

    // ═══════════════════════════════════════
    // PLUS CODE
    // ═══════════════════════════════════════
    function parsePlusCode(input) {
        const OLC_CHARS = '[23456789CFGHJMPQRVWX]';
        const fullPattern  = new RegExp(`(${OLC_CHARS}{4,8}\\+${OLC_CHARS}{2,})`, 'i');
        const shortPattern = new RegExp(`^\\s*(${OLC_CHARS}{4}\\+${OLC_CHARS}{2,})`, 'i');

        let match = input.match(fullPattern);
        if (match && match[1].length >= 8) {
            const afterCode = input.replace(match[1], '').replace(/^[,\s]+/, '').trim();
            return { code: match[1].toUpperCase(), locality: afterCode || null, isShort: false };
        }

        match = input.match(shortPattern);
        if (match) {
            const rest = input.slice(match[0].length).replace(/^[,\s]+/, '').trim();
            return { code: match[1].toUpperCase(), locality: rest || null, isShort: true };
        }

        return null;
    }

    async function decodePlusCode(parsed) {
        const olc = window.OpenLocationCode || window.openlocationcode || window.openLocationCode;
        if (!olc) { showStatus('Plus Code library not loaded. Falling back to address search.', 'error'); return null; }

        try {
            if (!parsed.isShort) {
                if (!olc.isValid(parsed.code)) { showStatus('Invalid Plus Code format.', 'error'); return null; }
                const ca = olc.decode(parsed.code);
                return { lat: ca.latitudeCenter, lng: ca.longitudeCenter };
            }

            if (!parsed.locality) {
                showStatus('Short Plus Code needs a reference. Try: "972F+JH4, Sibulan"', 'error');
                return null;
            }

            const localityParts = parsed.locality.split(',').map(s => s.trim()).filter(Boolean);
            let refCoords = null, resolvedWith = null;

            for (let i = 0; i < localityParts.length; i++) {
                const candidate = localityParts.slice(i).join(', ') + ', Philippines';
                showStatus(`Resolving reference: ${candidate}…`, 'loading');
                refCoords = await nominatimGeocode(candidate);
                if (refCoords) { resolvedWith = candidate; break; }
            }

            if (!refCoords) {
                showStatus(`Could not resolve "${parsed.locality}". Try: "<strong>${parsed.code}, Sibulan</strong>"`, 'error');
                return null;
            }

            const fullCode = olc.recoverNearest(parsed.code, refCoords.lat, refCoords.lng);
            if (!olc.isValid(fullCode)) { showStatus('Could not decode Plus Code with given reference.', 'error'); return null; }

            const ca = olc.decode(fullCode);
            parsed._resolvedWith = resolvedWith;
            return { lat: ca.latitudeCenter, lng: ca.longitudeCenter };

        } catch (err) {
            console.error('Plus Code decode error:', err);
            showStatus('Plus Code decoding failed. Searching as regular address instead.', 'error');
            return null;
        }
    }

    // ═══════════════════════════════════════
    // MAIN GEOCODE FUNCTION
    // ═══════════════════════════════════════
    async function geocodeQuery(rawQuery) {
        showStatus('Searching…', 'loading');
        rawQuery = rawQuery.replace(/\s+/g, ' ').trim();

        const parsed = parsePlusCode(rawQuery);

        if (parsed) {
            showStatus(
                parsed.isShort
                    ? `Decoding short Plus Code <strong>${parsed.code}</strong> with reference: ${parsed.locality || '?'}…`
                    : `Decoding Plus Code <strong>${parsed.code}</strong>…`,
                'loading'
            );
            const coords = await decodePlusCode(parsed);
            if (coords) {
                const refNote = parsed._resolvedWith
                    ? ` (resolved via <em>${parsed._resolvedWith.replace(', Philippines', '')}</em>)` : '';
                setMarker(coords.lat, coords.lng);
                showStatus(`📍 Plus Code <strong>${parsed.code}</strong>${refNote} → ${coords.lat.toFixed(6)}, ${coords.lng.toFixed(6)}`, 'success');
                return;
            }
        }

        let result = await nominatimGeocode(rawQuery);

        if (!result) {
            const parts = rawQuery.split(',').map(p => p.trim());
            const cityProvince = parts.slice(-2).join(', ');
            if (cityProvince && cityProvince !== rawQuery) {
                showStatus(`Trying city/province: ${cityProvince}…`, 'loading');
                result = await nominatimGeocode(cityProvince);
            }
        }

        if (!result) {
            showStatus(`No results for "<strong>${rawQuery}</strong>". Try clicking directly on the map.`, 'error');
            return;
        }

        setMarker(result.lat, result.lng);
        const displayMessage = result.note
            ? `📍 ${result.note}: ${result.display.substring(0, 60)}…`
            : `📍 Found: ${result.display.substring(0, 80)}${result.display.length > 80 ? '…' : ''}`;
        showStatus(displayMessage, 'success');

        if (!cityInput.value.trim() && result.display.includes(',')) {
            const parts = result.display.split(',');
            if (parts.length >= 2) {
                const lastTwo = parts.slice(-2).map(p => p.trim().replace('Philippines', '').trim());
                if (!cityInput.value.trim()) cityInput.value = lastTwo[0];
                if (!provinceInput.value.trim()) provinceInput.value = lastTwo[1];
            }
        }
    }

    // ═══════════════════════════════════════
    // MAP BUTTON HANDLERS
    // ═══════════════════════════════════════
    document.getElementById('btn-search-map').addEventListener('click', function () {
        const q = searchInput.value.trim();
        if (!q) { showStatus('Please enter an address or Plus Code to search.', 'error'); return; }
        geocodeQuery(q);
    });

    searchInput.addEventListener('keypress', function (e) {
        if (e.key === 'Enter') { e.preventDefault(); document.getElementById('btn-search-map').click(); }
    });

    document.getElementById('btn-locate-address').addEventListener('click', function () {
        const parts = [
            addressInput.value.trim(),
            barangayInput.value.trim(),
            cityInput.value.trim(),
            zipInput.value.trim(),
            provinceInput.value.trim()
        ].filter(Boolean);

        if (parts.length < 2) {
            showStatus('Please fill in at least Address and City fields first.', 'error');
            return;
        }
        geocodeQuery(parts.join(', '));
    });

    clearBtn.addEventListener('click', function () {
        if (marker) { map.removeLayer(marker); marker = null; }
        latInput.value  = '';
        lngInput.value  = '';
        coordDisplay.innerHTML = '<i class="bi bi-pin-map me-1"></i> No location set';
        coordDisplay.className = 'badge bg-primary-subtle text-primary px-3 py-2';
        clearBtn.style.display = 'none';
        map.setView([DEFAULT_LAT, DEFAULT_LNG], 13);
    });

    // Restore pin on validation error reload
    const oldLat = latInput.value;
    const oldLng = lngInput.value;
    if (oldLat && oldLng) setMarker(oldLat, oldLng);

    // Fix Leaflet rendering inside Bootstrap cards
    setTimeout(() => map.invalidateSize(), 300);

    // ═══════════════════════════════════════
    // GCASH QR UPLOAD & PREVIEW
    // ═══════════════════════════════════════
    const qrDropzone    = document.getElementById('qr-dropzone');
    const qrInput       = document.getElementById('gcash_qr');
    const qrPlaceholder = document.getElementById('qr-placeholder');
    const qrPreviewWrap = document.getElementById('qr-preview-wrap');
    const qrPreviewImg  = document.getElementById('qr-preview-img');
    const btnRemoveQr   = document.getElementById('btn-remove-qr');
    const gcashNumberEl = document.getElementById('gcash_number');
    const gcashNameEl   = document.getElementById('gcash_name');
    const gcashSummary  = document.getElementById('gcash-summary');
    const summaryName   = document.getElementById('gcash-summary-name');
    const summaryNumber = document.getElementById('gcash-summary-number');

    // Click dropzone → trigger file picker
    qrDropzone.addEventListener('click', function (e) {
        if (e.target === btnRemoveQr || btnRemoveQr.contains(e.target)) return;
        qrInput.click();
    });

    // Drag & drop
    qrDropzone.addEventListener('dragover', function (e) {
        e.preventDefault();
        qrDropzone.style.borderColor = '#2563eb';
        qrDropzone.style.backgroundColor = 'rgba(37,99,235,0.04)';
    });
    qrDropzone.addEventListener('dragleave', function () {
        qrDropzone.style.borderColor = '';
        qrDropzone.style.backgroundColor = '';
    });
    qrDropzone.addEventListener('drop', function (e) {
        e.preventDefault();
        qrDropzone.style.borderColor = '';
        qrDropzone.style.backgroundColor = '';
        if (e.dataTransfer.files[0]) {
            qrInput.files = e.dataTransfer.files;
            showQrPreview(e.dataTransfer.files[0]);
        }
    });

    qrInput.addEventListener('change', function () {
        if (this.files[0]) showQrPreview(this.files[0]);
    });

    function showQrPreview(file) {
        if (!file.type.match(/image\/(png|jpeg|jpg)/)) {
            alert('Please upload a PNG or JPG image only.');
            return;
        }
        if (file.size > 2 * 1024 * 1024) {
            alert('Image must be under 2MB.');
            return;
        }
        const reader = new FileReader();
        reader.onload = function (e) {
            qrPreviewImg.src = e.target.result;
            qrPlaceholder.style.display = 'none';
            qrPreviewWrap.style.display = 'block';
        };
        reader.readAsDataURL(file);
    }

    btnRemoveQr.addEventListener('click', function (e) {
        e.stopPropagation();
        qrInput.value           = '';
        qrPreviewImg.src        = '#';
        qrPlaceholder.style.display = 'block';
        qrPreviewWrap.style.display = 'none';
    });

    // GCash number auto-format + summary
    gcashNumberEl.addEventListener('input', function (e) {
        let v = e.target.value.replace(/\D/g, '').slice(0, 11);
        if (v.length > 7)      v = v.slice(0, 4) + ' ' + v.slice(4, 7) + ' ' + v.slice(7);
        else if (v.length > 4) v = v.slice(0, 4) + ' ' + v.slice(4);
        e.target.value = v;
        updateGcashSummary();
    });

    gcashNameEl.addEventListener('input', updateGcashSummary);

    function updateGcashSummary() {
        const name = gcashNameEl.value.trim();
        const num  = gcashNumberEl.value.trim();
        if (name || num) {
            summaryName.textContent   = name || '—';
            summaryNumber.textContent = num  || '—';
            gcashSummary.style.display = 'block';
        } else {
            gcashSummary.style.display = 'none';
        }
    }

    // Restore GCash summary on validation error reload
    updateGcashSummary();
});
</script>
@endpush

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    /* ── Base card ───────────────────────────── */
    .card {
        background-color: #ffffff;
        color: #212529;
    }
    .card-body,
    .card-header {
        background-color: #ffffff;
    }

    /* ── Dark mode: cards ───────────────────── */
    [data-theme="dark"] .card {
        background-color: #1e293b !important;
        border-color: #334155 !important;
        color: #f1f5f9 !important;
    }
    [data-theme="dark"] .card-body,
    [data-theme="dark"] .card-header {
        background-color: #1e293b !important;
        color: #f1f5f9 !important;
    }
    [data-theme="dark"] .card-header.bg-light {
        background-color: rgba(255, 255, 255, 0.05) !important;
    }
    [data-theme="dark"] .card h5,
    [data-theme="dark"] .card h6,
    [data-theme="dark"] .card .fw-bold,
    [data-theme="dark"] .card .fw-semibold {
        color: #f1f5f9 !important;
    }

    /* ── Dark mode: utilities ───────────────── */
    [data-theme="dark"] .bg-light {
        background-color: rgba(255, 255, 255, 0.05) !important;
    }
    [data-theme="dark"] .bg-white {
        background-color: #1e293b !important;
    }

    /* ── Dark mode: form controls ───────────── */
    [data-theme="dark"] .form-control,
    [data-theme="dark"] .form-select {
        background-color: #334155;
        border-color: #475569;
        color: #f1f5f9;
    }
    [data-theme="dark"] .form-control:focus,
    [data-theme="dark"] .form-select:focus {
        background-color: #334155;
        border-color: #2563eb;
        color: #f1f5f9;
        box-shadow: 0 0 0 0.2rem rgba(37,99,235,0.25);
    }
    [data-theme="dark"] .form-control::placeholder {
        color: #94a3b8;
    }
    [data-theme="dark"] .input-group-text {
        background-color: rgba(37, 99, 235, 0.15) !important;
        border-color: #475569;
    }

    /* ── Dark mode: alerts ──────────────────── */
    [data-theme="dark"] .alert-info {
        background-color: rgba(59, 130, 246, 0.1);
        border-color: rgba(59, 130, 246, 0.2);
        color: #93c5fd;
    }
    [data-theme="dark"] .alert-success {
        background-color: rgba(16, 185, 129, 0.1);
        border-color: rgba(16, 185, 129, 0.2);
        color: #6ee7b7;
    }
    [data-theme="dark"] .alert-warning {
        background-color: rgba(245, 158, 11, 0.1);
        border-color: rgba(245, 158, 11, 0.2);
        color: #fcd34d;
    }

    /* ── Dark mode: GCash card specific ─────── */
    [data-theme="dark"] .qr-dropzone-box {
        border-color: #475569 !important;
        background-color: transparent;
    }
    [data-theme="dark"] .qr-dropzone-box:hover {
        border-color: #2563eb !important;
        background-color: rgba(37, 99, 235, 0.05) !important;
    }
    [data-theme="dark"] .gcash-summary-box {
        background-color: rgba(255, 255, 255, 0.04) !important;
        border-color: #334155 !important;
    }

    /* ── Dark mode: code tags ───────────────── */
    code {
        background: #f3f4f6;
        padding: 1px 5px;
        border-radius: 4px;
        font-size: 0.8rem;
        color: #3D3B6B;
    }
    [data-theme="dark"] code {
        background: rgba(255, 255, 255, 0.1);
        color: #93c5fd;
    }

    /* ── Typography ─────────────────────────── */
    .form-label  { font-size: 0.9rem; font-weight: 600; }
    .text-muted  { font-size: 0.85rem; }
    .card-header { padding: 0.75rem 1.25rem; }

    /* ── Map ────────────────────────────────── */
    #branch-map { cursor: crosshair; z-index: 1; }

    /* ── GCash QR dropzone ──────────────────── */
    .qr-dropzone-box {
        border-style: dashed !important;
        border-color: #c3cfe2 !important;
        transition: border-color 0.2s, background-color 0.2s;
        cursor: pointer;
    }
    .qr-dropzone-box:hover {
        border-color: #2563eb !important;
        background-color: rgba(37, 99, 235, 0.03);
    }
    .gcash-summary-box {
        background-color: #f8fafc;
    }

    /* ── Geocode status colors ──────────────── */
    .geocode-success { color: #10B981; }
    .geocode-error   { color: #EF4444; }
    .geocode-loading { color: #6366F1; }

    /* ── Spinner ────────────────────────────── */
    @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
    .spinner-rotate { display: inline-block; animation: spin 1s linear infinite; }

    /* ── Mobile ─────────────────────────────── */
    @media (max-width: 768px) {
        .input-group {
            flex-wrap: wrap;
            gap: 0.35rem;
        }
        .input-group > * {
            width: 100%;
            border-radius: 0.375rem !important;
        }
    }
</style>
@endpush
