@forelse($captures as $capture)
    <div class="col-6 col-sm-4 col-md-3 col-lg-2 mb-2 px-1 capture-item-card" 
         data-trained="{{ $capture->capture_is_trained ? 'yes' : 'no' }}" 
         data-status="{{ $capture->capture_ai_status ?: 'unknown' }}">
        <div class="card p-1 m-0 position-relative bg-white capture-card">
            <div class="position-absolute" style="top: 5px; left: 5px; z-index: 5;">
                <input type="checkbox" name="capture_ids[]" value="{{ $capture->capture_id }}" onchange="updateSelectedCount()" class="capture-checkbox" style="width: 15px; height: 15px; cursor: pointer;">
            </div>
            
            <a href="{{ asset('storage/' . $capture->capture_image_path) }}" target="_blank" title="Klik untuk perbesar">
                <img src="{{ asset('storage/' . $capture->capture_image_path) }}" class="img-fluid rounded" style="height: 110px; width: 100%; object-fit: cover;">
            </a>
            
            <div class="mt-2 text-left" style="font-size: 10px; line-height: 1.4; padding: 0 4px;">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="text-muted font-weight-bold" style="font-size: 9px;"><i class="fas fa-robot mr-1 text-secondary"></i> CV:</span>
                    @if($capture->capture_ai_status === 'banyak')
                        <span class="status-pill status-pill-banyak">Banyak</span>
                    @elseif($capture->capture_ai_status === 'terbatas')
                        <span class="status-pill status-pill-terbatas">Terbatas</span>
                    @elseif($capture->capture_ai_status === 'penuh')
                        <span class="status-pill status-pill-penuh">Penuh</span>
                    @else
                        <span class="status-pill status-pill-pending">Pending</span>
                    @endif
                </div>
                
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="text-muted font-weight-bold" style="font-size: 9px;"><i class="fas fa-user-check mr-1 text-secondary"></i> Val:</span>
                    @if($capture->userValidation)
                        @if($capture->userValidation->user_validation_content === 'banyak')
                            <span class="status-pill status-pill-banyak">Banyak</span>
                        @elseif($capture->userValidation->user_validation_content === 'terbatas')
                            <span class="status-pill status-pill-terbatas">Terbatas</span>
                        @elseif($capture->userValidation->user_validation_content === 'penuh')
                            <span class="status-pill status-pill-penuh">Penuh</span>
                        @endif
                    @else
                        <span class="status-pill status-pill-belum">Belum</span>
                    @endif
                </div>

                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted font-weight-bold" style="font-size: 9px;"><i class="fas fa-brain mr-1 text-secondary"></i> Latih:</span>
                    @if($capture->capture_is_trained)
                        <span class="status-pill status-pill-trained"><i class="fas fa-check mr-1"></i> Trained</span>
                    @else
                        <span class="status-pill status-pill-new"><i class="fas fa-plus mr-1"></i> New</span>
                    @endif
                </div>

                <div class="text-muted text-right font-italic" style="font-size: 8px; border-top: 1px solid #f1f1f1; padding-top: 4px;" title="{{ $capture->created_at->format('d/m/Y H:i:s') }}">
                    <i class="far fa-clock mr-1"></i>{{ $capture->created_at->format('d/m/Y H:i:s') }}
                </div>
            </div>
        </div>
    </div>
@empty
    <div class="col-md-12 text-center text-muted py-4" id="no-captures-placeholder">
        <i class="fas fa-images fa-2x mb-2 text-muted"></i>
        <p class="mb-0 small">Belum ada snapshot gambar yang dikumpulkan.</p>
    </div>
@endforelse
