@extends('layouts.app-simple')

@section('title', $cloth->title)

@section('styles')
<link rel="stylesheet" href="{{ asset('css/product.css') }}" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="{{ asset('css/cloth-show.css') }}">
@endsection

@section('content')
<div id="alert-container"></div>

<!-- Measurements Modal -->
<div class="modal fade" id="measurementsModal" tabindex="-1" aria-labelledby="measurementsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content rounded-4 border-0 shadow">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title font-weight-bold" id="measurementsModalLabel">Measurements</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body pt-2">
        <p class="text-muted small mb-3">All measurements are in inches.</p>
        <ul class="list-group list-group-flush">
          <li class="list-group-item d-flex justify-content-between align-items-center px-0">
            <span>Chest / Bust</span>
            <span class="font-weight-bold text-dark">{{ $cloth->chest_bust ?? '—' }}"</span>
          </li>
          <li class="list-group-item d-flex justify-content-between align-items-center px-0">
            <span>Waist</span>
            <span class="font-weight-bold text-dark">{{ $cloth->waist ?? '—' }}"</span>
          </li>
          <li class="list-group-item d-flex justify-content-between align-items-center px-0">
            <span>Length</span>
            <span class="font-weight-bold text-dark">{{ $cloth->length ?? '—' }}"</span>
          </li>
          <li class="list-group-item d-flex justify-content-between align-items-center px-0">
            <span>Shoulder</span>
            <span class="font-weight-bold text-dark">{{ $cloth->shoulder ?? '—' }}"</span>
          </li>
          <li class="list-group-item d-flex justify-content-between align-items-center px-0">
            <span>Sleeve Length</span>
            <span class="font-weight-bold text-dark">{{ $cloth->sleeve_length ?? '—' }}"</span>
          </li>
        </ul>
      </div>
    </div>
  </div>
</div>

<section class="product-hero container">
  <div class="row g-4 align-items-start">
    <div class="col-lg-7">
      <div class="product-gallery card shadow-sm">
        <div class="product-gallery__main">
          @if($cloth->images->count())
            <img src="{{ asset('storage/' . $cloth->images->first()->image_path) }}" alt="{{ $cloth->title }}" class="img-fluid rounded-4 w-100" id="activeProductImage" style="cursor: pointer;">
          @else
            <img src="{{ asset('images/lehenga.jpg') }}" alt="{{ $cloth->title }}" class="img-fluid rounded-4 w-100">
          @endif
          @if($cloth->is_approved === 1)
          <div class="floating-badge">
            <i class="bi bi-shield-check"></i> QC Passed
          </div>
          @endif
        </div>
        @if($cloth->images->count() > 1)
          <div class="product-gallery__thumbs">
            @foreach($cloth->images as $image)
              <button class="thumb" data-image="{{ asset('storage/' . $image->image_path) }}">
                <img src="{{ asset('storage/' . $image->image_path) }}" alt="thumb">
              </button>
            @endforeach
          </div>
        @endif
      </div>

      <div class="card shadow-sm mt-4 p-4">
        <div class="d-flex flex-wrap gap-2 mb-3">
          <span class="chip"><i class="bi bi-grid"></i>{{ $cloth->category->name ?? 'General' }}</span>
          <span class="chip"><i class="bi bi-person"></i>{{ $cloth->gender }}</span>
          <span class="chip"><i class="bi bi-droplet-half"></i>{{ $cloth->color->name ?? 'Not specified' }}</span>
          <span class="chip"><i class="bi bi-rulers"></i>Size {{ $cloth->size->name ?? $cloth->size }}</span>
          <span class="chip"><i class="bi bi-scissors"></i>{{ $cloth->fabric->name ?? 'Fabric TBC' }}</span>
        </div>

        <h1 class="product-title">{{ $cloth->title }}</h1>
        <p class="text-muted mb-2">{{ $cloth->brand->name ?? 'Independent Designer' }}</p>
        
        <!-- @if($cloth->user && $cloth->user->average_rating > 0)
          <div class="mb-4">
              <span class="badge badge-light border text-warning border-warning" title="Seller Rating">
                  <i class="bi bi-star-fill text-warning"></i> Seller Rating: {{ $cloth->user->average_rating }}
              </span>
          </div>
        @else
          <div class="mb-4"></div>
        @endif -->

        <div class="info-grid">
          <div>
            <label>Fit Type</label>
            <p>{{ $cloth->fitType->name ?? 'Regular fit' }}</p>
          </div>
          <div>
            <label>Condition</label>
            <p>{{ $cloth->condition->name ?? $cloth->condition }}</p>
          </div>
          <div>
            <label class="d-flex align-items-center mb-1">
              Measurements 
              <button type="button" class="btn btn-link p-0 ms-2 text-primary" data-toggle="modal" data-target="#measurementsModal" style="margin-left: 5px; line-height: 1;">
                <i class="bi bi-info-circle" style="font-size: 0.9rem;"></i>
              </button>
            </label>
            <p>Chest {{ $cloth->chest_bust ?? '—' }}, Waist {{ $cloth->waist ?? '—' }}</p>
          </div>
          <!-- <div>
            <label>Care</label>
            <p>{{ $cloth->is_cleaned ? 'Dry-cleaned & ready' : 'Freshly steamed' }}</p>
          </div> -->
        </div>

        <div class="detail-grid">
          <div>
            <h6>Fabric & Highlights</h6>
            <p class="mb-0">
              {{ $cloth->fabric->name ?? 'Premium blended fabric' }} · {{ ucfirst(strtolower($cloth->color->name ?? 'Multi')) }} tone · {{ $cloth->bottomType->name ?? 'Two-piece' }} silhouette
            </p>
          </div>
          <div>
            <h6>Notes</h6>
            <p class="mb-0">{{ $cloth->defects ?? 'No visible flaws reported by the owner.' }}</p>
          </div>
        </div>
      </div>

      <div class="card shadow-sm mt-4 p-4 availability-card">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h5 class="mb-0"><i class="bi bi-calendar-week me-2 text-primary"></i>Availability</h5>
          <span class="badge bg-light text-dark">{{ $cloth->availabilityBlocks->where('type','available')->count() ? '' : 'Always available' }}</span>
        </div>
        @if($cloth->availabilityBlocks->where('type', 'available')->count() > 0)
          <div class="timeline">
            @foreach($cloth->availabilityBlocks->where('type', 'available') as $block)
              <div class="timeline__item">
                <span>{{ \Carbon\Carbon::parse($block->start_date)->format('d/m/Y') }}</span>
                <span class="text-muted">to</span>
                <span>{{ \Carbon\Carbon::parse($block->end_date)->format('d/m/Y') }}</span>
              </div>
            @endforeach
          </div>
        @else
          <p class="text-muted mb-0">This outfit is rental-ready every day of the year.</p>
        @endif

        <!-- @if($cloth->availabilityBlocks->where('type', 'blocked')->count() > 0)
          <div class="timeline timeline--blocked mt-3">
            @foreach($cloth->availabilityBlocks->where('type', 'blocked') as $block)
              <div class="timeline__item">
                <span>{{ \Carbon\Carbon::parse($block->start_date)->format('d/m/Y') }}</span>
                <span class="text-muted">to</span>
                <span>{{ \Carbon\Carbon::parse($block->end_date)->format('d/m/Y') }}</span>
                @if($block->reason)
                  <small class="text-muted d-block">{{ $block->reason }}</small>
                @endif
              </div>
            @endforeach
          </div>
        @endif -->
      </div>

      <div class="card shadow-sm mt-4 p-4">
        <h5 class="mb-3"><i class="bi bi-pencil-square me-2 text-primary"></i>Plan your rental</h5>
        <div class="row g-3">
          <div class="col-md-6">
            <label for="start_date" class="form-label">Start Date *</label>
            <input type="text" class="form-control bg-white" id="start_date" name="start_date" placeholder="Select Start Date" readonly="readonly" required>
          </div>
          <div class="col-md-6">
            <label for="end_date" class="form-label">End Date *</label>
            <input type="text" class="form-control bg-white" id="end_date" name="end_date" placeholder="Select End Date" readonly="readonly" required>
          </div>
        </div>
        <div class="rental-summary card mt-3" id="rental-summary" style="display:none;">
          <div class="card-body">
            <div class="d-flex justify-content-between mb-1">
              <span>Rental Duration</span>
              <strong id="rental-details-duration">0 days</strong>
            </div>
            <div id="rental-cost-breakdown"></div>
            <div class="d-flex justify-content-between mb-1">
              <span>Rental Cost</span>
              <strong id="rental-details-cost">₹0</strong>
            </div>
            <div class="d-flex justify-content-between mb-1">
              <span>Refundable Security</span>
              <strong>₹{{ number_format($cloth->security_deposit) }}</strong>
            </div>
            <hr>
            <div class="d-flex justify-content-between align-items-center">
              <span>Total due today</span>
              <span class="total-price">₹<span id="total-price">0</span></span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-5">
      <div class="sticky-lg-top" style="top: 90px;">
        <div class="summary card shadow-lg border-0">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-start">
              <div>
                <p class="text-uppercase text-muted small mb-1">Rent for 4 days</p>
                <h2 class="mb-0 text-primary">₹{{ number_format($cloth->rent_price) }}</h2>
                <p class="text-muted small mb-1">₹{{ number_format($cloth->rent_price / 4) }} per day (after 4 days)</p>
                <p class="text-muted small mb-3">Security deposit ₹{{ number_format($cloth->security_deposit) }}</p>
              </div>
              <div class="text-end">
                @if($cloth->user && $cloth->user->average_rating > 0)
                  <span class="badge bg-white text-warning border border-warning">
                    <i class="bi bi-star-fill me-1"></i> {{ $cloth->user->average_rating }} Seller Rating
                  </span>
                @else
                  <span class="badge bg-success-subtle text-success">
                    <i class="bi bi-star-fill me-1"></i> Trusted owner
                  </span>
                @endif
              </div>
            </div>

            @if($cloth->sku > 0)
              <button class="rent-button add-to-cart-btn w-100" data-cloth-id="{{ $cloth->id }}" id="productRentBtn" disabled>
                <i class="bi bi-cart-plus me-2"></i>Select dates to rent
              </button>

              @if($cloth->is_purchased)
                <button class="buy-button add-to-cart-buy-btn w-100 mt-2" data-cloth-id="{{ $cloth->id }}" id="productBuyBtn">
                  <i class="bi bi-bag-check me-2"></i>Buy once - ₹{{ number_format($cloth->purchase_value) }}
                </button>
              @endif
            @else
              <button class="btn btn-secondary w-100" disabled>
                <i class="bi bi-x-circle me-2"></i>Sold Out
              </button>
            @endif

            <ul class="assurance-list mt-4">
              <li><i class="bi bi-truck"></i> Free insured delivery & pickup</li>
              <li><i class="bi bi-brush"></i> Complimentary dry-cleaning</li>
              <li><i class="bi bi-arrow-repeat"></i> Instant refund for cancellations up to 48h</li>
            </ul>
          </div>
        </div>

        <!-- <div class="support-card card shadow-sm mt-3">
          <div class="card-body d-flex align-items-center gap-3">
            <div class="support-icon">
              <i class="bi bi-headset"></i>
            </div>
            <div>
              <p class="mb-0 fw-semibold">Need styling help?</p>
              <small class="text-muted">Chat with our concierge on WhatsApp</small>
            </div>
            <a href="https://wa.me/15551234567" class="btn btn-outline-primary btn-sm">Chat</a>
          </div>
        </div> -->
      </div>
    </div>
  </div>
</section>

<section class="reviews-questions mt-5">
  <div class="container">
    <div class="row">
      <div class="col-12">
        <!-- Reviews Section -->
        <div class="card shadow-sm mb-4">
          <div class="card-body">
            <h5 class="card-title mb-4">
              <i class="bi bi-star-fill text-warning me-2"></i>
              Reviews & Ratings
              @if($cloth->reviews->count() > 0)
                <span class="badge bg-primary ms-2">{{ $cloth->reviews->count() }}</span>
              @endif
            </h5>

            <!-- Average Rating -->
            @if($cloth->reviews->count() > 0)
            <div class="mb-4">
              <div class="d-flex align-items-center mb-2">
                <h3 class="mb-0 me-3">{{ number_format($cloth->average_rating, 1) }}</h3>
                <div class="star-rating-display">
                  @for($i = 1; $i <= 5; $i++)
                    @if($i <= floor($cloth->average_rating))
                      <i class="bi bi-star-fill text-warning"></i>
                    @elseif($i - 0.5 <= $cloth->average_rating)
                      <i class="bi bi-star-half text-warning"></i>
                    @else
                      <i class="bi bi-star text-warning"></i>
                    @endif
                  @endfor
                </div>
                <span class="text-muted ms-2">({{ $cloth->reviews->count() }} {{ Str::plural('review', $cloth->reviews->count()) }})</span>
              </div>
            </div>
            @endif

            <!-- Post Review Form (Only for logged in users) -->
            <!-- Post Review Form (Only for logged in users who purchased/rented) -->
            @if(Auth::check())
              @if($canReview)
              <div class="card border mb-4">
                <div class="card-body">
                  <h6 class="card-title">Write a Review</h6>
                  <form id="reviewForm">
                    @csrf
                    <div class="mb-3">
                      <label class="form-label">Rating *</label>
                      <div class="star-rating-input">
                        @for($i = 1; $i <= 5; $i++)
                          <i class="bi bi-star star-rating-star" data-rating="{{ $i }}"></i>
                        @endfor
                        <span class="rating-text ms-2 text-muted"></span>
                      </div>
                      <input type="hidden" name="rating" id="rating" required>
                      <div id="rating-error" class="text-danger small" style="display:none;"></div>
                    </div>
                    <div class="mb-3">
                      <label for="review_text" class="form-label">Your Review</label>
                      <textarea class="form-control" id="review_text" name="review" rows="3" maxlength="1000" placeholder="Share your experience with this product..."></textarea>
                      <small class="text-muted"><span id="review_char_count">0</span>/1000 characters</small>
                    </div>
                    <button type="submit" class="btn btn-warning">
                      <i class="bi bi-send me-2"></i>Post Review
                    </button>
                  </form>
                </div>
              </div>
              @else
              <div class="alert alert-light border text-muted mb-4">
                <i class="bi bi-info-circle me-1"></i> Only users who have rented or purchased this item can leave a review.
              </div>
              @endif
            @else
            <div class="alert alert-info">
              <a href="{{ route('login') }}" class="alert-link">Login</a> to post a review and help others make better decisions.
            </div>
            @endif

            <!-- Existing Reviews -->
            <div id="reviews-list">
              @forelse($cloth->reviews->sortByDesc('created_at') as $review)
              <div class="review-item border-bottom pb-3 mb-3" data-review-id="{{ $review->id }}">
                <div class="d-flex justify-content-between align-items-start">
                  <div class="flex-grow-1">
                    <div class="d-flex align-items-center mb-2">
                      <strong>{{ $review->user->name }}</strong>
                      <div class="star-rating-display ms-2">
                        @for($i = 1; $i <= 5; $i++)
                          @if($i <= $review->rating)
                            <i class="bi bi-star-fill text-warning" style="font-size: 0.875rem;"></i>
                          @else
                            <i class="bi bi-star text-warning" style="font-size: 0.875rem;"></i>
                          @endif
                        @endfor
                      </div>
                      <span class="text-muted ms-2 small">{{ $review->created_at->diffForHumans() }}</span>
                    </div>
                    @if($review->review)
                    <p class="mb-0">{{ $review->review }}</p>
                    @endif
                  </div>
                  @auth
                  @if($review->user_id === Auth::id())
                  <button class="btn btn-sm btn-link text-danger delete-review-btn" data-review-id="{{ $review->id }}">
                    <i class="bi bi-trash"></i>
                  </button>
                  @endif
                  @endauth
                </div>

                <!-- Replies Section -->
                <div class="replies-section mt-3 ps-4 border-start">
                    @foreach($review->replies as $reply)
                        <div class="reply-item mb-2 bg-light p-2 rounded" data-reply-id="{{ $reply->id }}">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <strong>{{ $reply->user->name }}</strong>
                                    <span class="text-muted small ms-2">{{ $reply->created_at->diffForHumans() }}</span>
                                    <p class="mb-0 small mt-1">{{ $reply->message }}</p>
                                </div>
                                @auth
                                    @if($reply->user_id === Auth::id())
                                        <button class="btn btn-sm btn-link text-danger delete-reply-btn p-0" data-reply-id="{{ $reply->id }}">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    @endif
                                @endauth
                            </div>
                        </div>
                    @endforeach

                    @auth
                        <button class="btn btn-sm btn-link text-primary p-0 mt-1 toggle-reply-form">
                            <i class="bi bi-reply"></i> Reply
                        </button>
                        <form class="reply-form mt-2" style="display:none;" data-type="review" data-id="{{ $review->id }}">
                            @csrf
                            <div class="input-group">
                                <input type="text" name="message" class="form-control form-control-sm" placeholder="Write a reply..." required>
                                <button class="btn btn-sm btn-primary" type="submit">Post</button>
                            </div>
                        </form>
                    @endauth
                </div>
              </div>
              @empty
              <p class="text-muted">No reviews yet. Be the first to review this product!</p>
              @endforelse
            </div>
          </div>
        </div>

        <!-- Questions Section -->
        <div class="card shadow-sm">
          <div class="card-body">
            <h5 class="card-title mb-4">
              <i class="bi bi-question-circle-fill text-primary me-2"></i>
              Questions & Answers
              @if($cloth->questions->count() > 0)
                <span class="badge bg-primary ms-2">{{ $cloth->questions->count() }}</span>
              @endif
            </h5>

            <!-- Post Question Form (Only for logged in users) -->
            @auth
            <div class="card border mb-4">
              <div class="card-body">
                <h6 class="card-title">Ask a Question</h6>
                <form id="questionForm">
                  @csrf
                  <div class="mb-3">
                    <label for="question_text" class="form-label">Your Question *</label>
                    <textarea class="form-control" id="question_text" name="question" rows="2" maxlength="500" placeholder="Have a question about this product? Ask away..." required></textarea>
                    <small class="text-muted"><span id="question_char_count">0</span>/500 characters</small>
                    <div id="question-error" class="text-danger small" style="display:none;"></div>
                  </div>
                  <button type="submit" class="btn btn-primary">
                    <i class="bi bi-send me-2"></i>Post Question
                  </button>
                </form>
              </div>
            </div>
            @else
            <div class="alert alert-info mb-4">
              <a href="{{ route('login') }}" class="alert-link">Login</a> to ask a question about this product.
            </div>
            @endauth

            <!-- Existing Questions -->
            <div id="questions-list">
              @forelse($cloth->questions->sortByDesc('created_at') as $question)
              <div class="question-item border-bottom pb-3 mb-3" data-question-id="{{ $question->id }}">
                <div class="mb-2">
                  <div class="d-flex justify-content-between align-items-start">
                    <div class="flex-grow-1">
                      <strong class="text-primary">Q:</strong> 
                      <span>{{ $question->question }}</span>
                      <div class="text-muted small mt-1">
                        Asked by <strong>{{ $question->user->name }}</strong> • {{ $question->created_at->diffForHumans() }}
                      </div>
                    </div>
                    @auth
                    @if($question->user_id === Auth::id() || $question->cloth->user_id === Auth::id())
                    <button class="btn btn-sm btn-link text-danger delete-question-btn" data-question-id="{{ $question->id }}">
                      <i class="bi bi-trash"></i>
                    </button>
                    @endif
                    @endauth
                  </div>
                </div>
                
                @if($question->answer)
                <div class="answer-box bg-light p-3 rounded ms-3">
                  <strong class="text-success">A:</strong> 
                  <span>{{ $question->answer }}</span>
                  <div class="text-muted small mt-1">
                    Answered by <strong>{{ $question->answerer->name ?? 'Owner' }}</strong> • {{ $question->answered_at->diffForHumans() }}
                  </div>
                </div>
                @else
                  @auth
                  @if($question->cloth->user_id === Auth::id())
                  <div class="answer-form ms-3 mt-2" style="display:none;">
                    <form class="answer-question-form">
                      @csrf
                      <div class="mb-2">
                        <textarea class="form-control form-control-sm" name="answer" rows="2" placeholder="Type your answer here..." required></textarea>
                      </div>
                      <button type="submit" class="btn btn-sm btn-success">Post Answer</button>
                      <button type="button" class="btn btn-sm btn-secondary cancel-answer-btn">Cancel</button>
                    </form>
                  </div>
                  <button class="btn btn-sm btn-outline-success answer-question-btn ms-3">
                    <i class="bi bi-reply me-1"></i>Answer
                  </button>
                  @endif
                  @endauth
                @endif

                <!-- Replies Section -->
                <div class="replies-section mt-3 ps-4 border-start">
                    @foreach($question->replies as $reply)
                        <div class="reply-item mb-2 bg-light p-2 rounded" data-reply-id="{{ $reply->id }}">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <strong>{{ $reply->user->name }}</strong>
                                    <span class="text-muted small ms-2">{{ $reply->created_at->diffForHumans() }}</span>
                                    <p class="mb-0 small mt-1">{{ $reply->message }}</p>
                                </div>
                                @auth
                                    @if($reply->user_id === Auth::id())
                                        <button class="btn btn-sm btn-link text-danger delete-reply-btn p-0" data-reply-id="{{ $reply->id }}">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    @endif
                                @endauth
                            </div>
                        </div>
                    @endforeach

                    @auth
                        <button class="btn btn-sm btn-link text-primary p-0 mt-1 toggle-reply-form">
                            <i class="bi bi-reply"></i> Reply
                        </button>
                        <form class="reply-form mt-2" style="display:none;" data-type="question" data-id="{{ $question->id }}">
                            @csrf
                            <div class="input-group">
                                <input type="text" name="message" class="form-control form-control-sm" placeholder="Write a reply..." required>
                                <button class="btn btn-sm btn-primary" type="submit">Post</button>
                            </div>
                        </form>
                    @endauth
                </div>
              </div>
              @empty
              <p class="text-muted">No questions yet. Be the first to ask a question!</p>
              @endforelse
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Image Lightbox Modal -->
<div class="modal fade" id="imageLightboxModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content bg-transparent border-0">
      <div class="modal-body p-0 position-relative text-center">
        <img src="" id="lightboxImage" class="img-fluid rounded shadow-lg" style="max-height: 90vh;">
      </div>
    </div>
  </div>
</div>

<section class="related mt-5">
  <div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h4 class="mb-0">More like this</h4>
      <a href="{{ route('clothes.index', ['categories' => $cloth->category->id ?? $categoryId]) }}" class="btn btn-warning btn-sm">Browse all</a>
    </div>
    <div class="carousel mb-5">
      @if($relatedClothes->count() > 0)
      <div class="carousel-items p-2 d-flex gap-3" style="overflow-x: auto;">
        @foreach($relatedClothes as $related)
        <div class="card border-0 shadow-sm" style="min-width: 250px; cursor: pointer;" onclick="window.location.href='{{ route('clothes.show', $related->id) }}'">
          @if($related->images->count())
            <img src="{{ asset('storage/' . $related->images->first()->image_path) }}" class="card-img-top rounded-top" alt="{{ $related->title }}" style="height: 300px; object-fit: cover;">
          @else
            <img src="{{ asset('images/placeholder.jpg') }}" class="card-img-top rounded-top" alt="{{ $related->title }}" style="height: 300px; object-fit: cover;">
          @endif
          <div class="card-body p-3">
            <h6 class="card-title fw-bold mb-1 text-truncate">{{ $related->title }}</h6>
            <div class="d-flex justify-content-between align-items-center">
              <span class="text-primary fw-bold">₹{{ number_format($related->rent_price) }}<small class="text-muted fw-normal">/4 days</small></span>
              <span class="badge bg-light text-dark border">{{ $related->size->name ?? 'Free' }}</span>
            </div>
          </div>
        </div>
        @endforeach
      </div>
      @else
      <div class="carousel-items">
        <div class="item placeholder-card w-100 bg-light p-4 text-center rounded">
          <p class="mb-0 text-muted">No similar items found at the moment.</p>
        </div>
      </div>
      @endif
    </div>
  </div>
</section>
@endsection

@section('scripts')
<script src="{{ asset('js/product.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
// Cloth data for calculations
const clothData = {
    id: {{ $cloth->id }},
    rentPrice: {{ $cloth->rent_price }},
    securityDeposit: {{ $cloth->security_deposit }},
    availableBlocks: @json($cloth->availabilityBlocks->where('type', 'available')->values()),
    blockedBlocks: @json($cloth->availabilityBlocks->where('type', 'blocked')->values()),
    isAlwaysAvailable: {{ $cloth->availabilityBlocks->where('type', 'available')->count() == 0 ? 'true' : 'false' }}
};
$(document).ready(function() {
    
    // Load cart items on page load to check rented status
    loadCartItems();
    
    // Function to calculate all disabled dates as an array
    function getDisabledDatesArray() {
        const disabledDates = [];
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        
        // Collect all available dates
        const availableDates = new Set();
        
        if (!clothData.isAlwaysAvailable) {
            // Collect dates from available blocks
            clothData.availableBlocks.forEach(function(block) {
                const start = new Date(block.start_date);
                const end = new Date(block.end_date);
                let current = new Date(start);
                
                while (current <= end) {
                    if (current >= today) {
                        availableDates.add(current.toISOString().split('T')[0]);
                    }
                    current.setDate(current.getDate() + 1);
                }
            });
        }
        
        // Collect blocked dates
        const blockedDates = new Set();
        clothData.blockedBlocks.forEach(function(block) {
            const start = new Date(block.start_date);
            const end = new Date(block.end_date);
            let current = new Date(start);
            
            while (current <= end) {
                if (current >= today) {
                    blockedDates.add(current.toISOString().split('T')[0]);
                }
                current.setDate(current.getDate() + 1);
            }
        });
        
        // If always available, disable only blocked dates
        if (clothData.isAlwaysAvailable) {
            blockedDates.forEach(function(dateStr) {
                disabledDates.push(dateStr);
            });
        } else {
            // For managed calendar, disable all dates except available ones (for next 2 years)
            const maxDate = new Date(today);
            maxDate.setFullYear(maxDate.getFullYear() + 2);
            let checkDate = new Date(today);
            
            while (checkDate <= maxDate) {
                const dateStr = checkDate.toISOString().split('T')[0];
                // Disable if not in available dates OR if it's blocked
                if (!availableDates.has(dateStr) || blockedDates.has(dateStr)) {
                    disabledDates.push(dateStr);
                }
                checkDate.setDate(checkDate.getDate() + 1);
            }
        }
        
        return disabledDates;
    }
    
    // Base Flatpickr config - all dates visible, only unavailable disabled
    const disabledDatesList = getDisabledDatesArray();
    const commonFlatpickrConfig = {
        minDate: "today",
        dateFormat: "Y-m-d", // Standard database format
        altInput: true,
        altFormat: "F j, Y", // User friendly format
        disable: disabledDatesList  // Array of disabled dates (YYYY-MM-DD) matches dateFormat
    };
    
    // Initialize Flatpickr for start date
    const startDatePicker = flatpickr("#start_date", Object.assign({}, commonFlatpickrConfig, {
        onChange: function(selectedDates, dateStr, instance) {
            if (selectedDates.length > 0) {
                // Calculate end date (4 days after start date - minimum rental period)
                const startDate = new Date(selectedDates[0]);
                const autoEndDate = new Date(startDate);
                autoEndDate.setDate(autoEndDate.getDate() + 3); // +3 because we count both start and end days (4 days total)
                
                // Set minimum end date
                endDatePicker.set('minDate', autoEndDate);
                
                // Automatically set end date to 4 days after start date
                // Check if auto end date is available (not disabled)
                const autoEndDateStr = autoEndDate.toISOString().split('T')[0];
                if (disabledDatesList.indexOf(autoEndDateStr) === -1) {
                    // Set the end date automatically
                    endDatePicker.setDate(autoEndDate, true); // true to trigger onChange
                } else {
                    // If auto end date is disabled, find next available date
                    let nextAvailableDate = new Date(autoEndDate);
                    let foundAvailable = false;
                    const maxTries = 30; // Try up to 30 days ahead
                    let tries = 0;
                    
                    while (!foundAvailable && tries < maxTries) {
                        nextAvailableDate.setDate(nextAvailableDate.getDate() + 1);
                        const nextDateStr = nextAvailableDate.toISOString().split('T')[0];
                        if (disabledDatesList.indexOf(nextDateStr) === -1) {
                            endDatePicker.setDate(nextAvailableDate, true);
                            foundAvailable = true;
                        }
                        tries++;
                    }
                }
            } else {
                validateAndCalculateRental();
            }
        }
    }));
    
    // Initialize Flatpickr for end date
    const endDatePicker = flatpickr("#end_date", Object.assign({}, commonFlatpickrConfig, {
        onChange: function(selectedDates, dateStr, instance) {
            validateAndCalculateRental();
        }
    }));
    
    // Ensure cart functionality works on this page
    $('.add-to-cart-btn').click(function(e) {
        e.preventDefault();
        const clothId = $(this).data('cloth-id');
        const $btn = $(this);
        const originalText = $btn.text();
        
        // Get rental dates and cost (Format: YYYY-MM-DD)
        const startDateStr = $('#start_date').val();
        const endDateStr = $('#end_date').val();

        if (!startDateStr || !endDateStr) {
            showAlert('danger', 'Please select rental start and end dates');
            $btn.prop('disabled', true).html('<i class="bi bi-cart-plus me-2"></i>SELECT DATES TO RENT');
            return;
        }

        const startDate = new Date(startDateStr);
        const endDate = new Date(endDateStr);

        const daysDiff = Math.ceil((endDate - startDate) / (1000 * 60 * 60 * 24)) + 1; // Include both start and end days
        
        // Validate minimum rental period (4 days)
        const MIN_RENTAL_DAYS = 4;
        if (daysDiff < MIN_RENTAL_DAYS) {
            showAlert('danger', `Minimum rental period is ${MIN_RENTAL_DAYS} days. Please select dates covering at least ${MIN_RENTAL_DAYS} days.`);
            $btn.prop('disabled', true).html('<i class="bi bi-cart-plus me-2"></i>SELECT DATES TO RENT');
            return;
        }
        
        // Calculate rental cost based on 4-day minimum period
        const basePrice = clothData.rentPrice; // Price for 4 days
        const perDayRate = basePrice / MIN_RENTAL_DAYS; // Per day rate after 4 days
        
        let rentCost;
        if (daysDiff <= MIN_RENTAL_DAYS) {
            // For 4 days or less, use base price
            rentCost = basePrice;
        } else {
            // For more than 4 days: base price + additional days * per day rate
            const additionalDays = daysDiff - MIN_RENTAL_DAYS;
            const additionalCost = additionalDays * perDayRate;
            rentCost = basePrice + additionalCost;
        }
        
        if (rentCost <= 0) {
            showAlert('danger', 'Please select valid rental dates to calculate cost');
            $btn.prop('disabled', true).html('<i class="bi bi-cart-plus me-2"></i>SELECT DATES TO RENT');
            return;
        }
        
        // Show loading state
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Adding...');
        
        const requestData = {
            cloth_id: clothId,
            rental_start_date: startDateStr, // Already YYYY-MM-DD
            rental_end_date: endDateStr,     // Already YYYY-MM-DD
            total_rental_cost: rentCost,
            rental_days: daysDiff,
            _token: $('meta[name="csrf-token"]').attr('content')
        };
        
        $.ajax({
            url: '/cart/add',
            type: 'POST',
            data: requestData,
            success: function(response) {
                if (response.success) {
                    // Update cart count
                    updateCartCount(response.cartCount);
                    
                    // Show success message
                    showAlert('success', response.message);
                    
                    // Update all buttons for this item to "RENTED"
                    updateAllRentButtons(clothId, true);
                    
                    // Reload cart items to update the list
                    loadCartItems();
                    
                    // Update button state
                    $btn.prop('disabled', true).html('<i class="bi bi-check me-2"></i>RENTED');
                } else {
                    showAlert('danger', response.message);
                    $btn.prop('disabled', false).html('<i class="bi bi-cart-plus me-2"></i>RENT NOW');
                }
            },
            error: function(xhr, status, error) {
                if (xhr.status === 401) {
                    // User not logged in, redirect to login with intended redirect
                    window.location.href = "{{ route('login') }}?redirect=" + encodeURIComponent(window.location.href);
                } else if (xhr.status === 422) {
                    // Validation error
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.errors) {
                            const errorMessages = Object.values(response.errors).flat().join(', ');
                            showAlert('danger', 'Validation error: ' + errorMessages);
                        } else {
                            showAlert('danger', 'Please check your input and try again.');
                        }
                    } catch (e) {
                        showAlert('danger', 'An error occurred. Please try again.');
                    }
                } else {
                    showAlert('danger', 'An error occurred. Please try again.');
                }
                $btn.prop('disabled', false).html('<i class="bi bi-cart-plus me-2"></i>RENT NOW');
            }
                 });
     });
     
     // Buy button functionality
     $('.add-to-cart-buy-btn').click(function(e) {
         e.preventDefault();
         const clothId = $(this).data('cloth-id');
         const $btn = $(this);
         
         // Show loading state
         $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Processing...');
         
         const requestData = {
             cloth_id: clothId,
             purchase_type: 'buy',
             total_purchase_cost: {{ $cloth->purchase_value }},
             _token: $('meta[name="csrf-token"]').attr('content')
         };
         
         $.ajax({
             url: '/cart/add',
             type: 'POST',
             data: requestData,
             success: function(response) {
                 if (response.success) {
                     // Update cart count
                     updateCartCount(response.cartCount);
                     
                     // Show success message
                     showAlert('success', response.message);
                     
                     // Update all buttons for this item to "PURCHASED"
                     updateAllBuyButtons(clothId, true);
                     
                     // Reload cart items to update the list
                     loadCartItems();
                     
                     // Update button state
                     $btn.prop('disabled', true).html('<i class="bi bi-check me-2"></i>PURCHASED');
                 } else {
                     showAlert('danger', response.message);
                     $btn.prop('disabled', false).html('<i class="bi bi-bag-check me-2"></i>BUY NOW - ₹{{ number_format($cloth->purchase_value) }}');
                 }
             },
             error: function(xhr, status, error) {
                 if (xhr.status === 401) {
                     // User not logged in, redirect to login with intended redirect
                     window.location.href = "{{ route('login') }}?redirect=" + encodeURIComponent(window.location.href);
                 } else if (xhr.status === 422) {
                     // Validation error
                     try {
                         const response = JSON.parse(xhr.responseText);
                         if (response.errors) {
                             const errorMessages = Object.values(response.errors).flat().join(', ');
                             showAlert('danger', 'Validation error: ' + errorMessages);
                         } else {
                             showAlert('danger', 'An error occurred. Please try again.');
                         }
                     } catch (e) {
                         showAlert('danger', 'An error occurred. Please try again.');
                     }
                 } else {
                     showAlert('danger', 'An error occurred. Please try again.');
                 }
                 $btn.prop('disabled', false).html('<i class="bi bi-bag-check me-2"></i>BUY NOW - ₹{{ number_format($cloth->purchase_value) }}');
             }
         });
     });
 });

// Date validation and rental calculation
function validateAndCalculateRental() {
    const startDate = $('#start_date').val();
    const endDate = $('#end_date').val();
    const $rentButton = $('.add-to-cart-btn');
    const $rentalSummary = $('#rental-summary');
    const $rentalDuration = $('#rental-details-duration');
    const $rentalCost = $('#rental-details-cost');
    const $totalPrice = $('#total-price');
    
    if (!startDate || !endDate) {
        $rentButton.prop('disabled', true).html('<i class="bi bi-cart-plus me-2"></i>Select dates to rent');
        $rentButton.prop('disabled', true).html('<i class="bi bi-cart-plus me-2"></i>Select dates to rent');
        $rentalSummary.hide();
        return;
    }
    
    const start = new Date(startDate);
    const end = new Date(endDate);
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    
    // Basic validation
    if (start < today) {
        showAlert('danger', 'Start date cannot be in the past');
        $rentButton.prop('disabled', true);
        $rentalSummary.hide();
        return;
    }
    
    if (end <= start) {
        showAlert('danger', 'End date must be after start date');
        $rentButton.prop('disabled', true);
        $rentalSummary.hide();
        return;
    }
    
    // Calculate number of days
    const daysDiff = Math.ceil((end - start) / (1000 * 60 * 60 * 24)) + 1; // Include both start and end days
    
    // Validate minimum rental period (4 days)
    const MIN_RENTAL_DAYS = 4;
    if (daysDiff < MIN_RENTAL_DAYS) {
        showAlert('danger', `Minimum rental period is ${MIN_RENTAL_DAYS} days. Please select dates covering at least ${MIN_RENTAL_DAYS} days.`);
        $rentButton.prop('disabled', true);
        $rentalSummary.hide();
        return;
    }
    
    // Check availability
    if (!clothData.isAlwaysAvailable) {
        const isAvailable = checkAvailability(start, end);
        if (!isAvailable.available) {
            showAlert('danger', isAvailable.message);
            $rentButton.prop('disabled', true);
            $rentalSummary.hide();
            return;
        }
    }
    
    // Check blocked dates
    const blockedCheck = checkBlockedDates(start, end);
    if (!blockedCheck.available) {
        showAlert('danger', blockedCheck.message);
        $rentButton.prop('disabled', true);
        $rentalSummary.hide();
        return;
    }
    
    // Calculate prices based on 4-day minimum period
    const basePrice = clothData.rentPrice; // Price for 4 days
    const perDayRate = basePrice / MIN_RENTAL_DAYS; // Per day rate after 4 days
    
    let rentCost;
    let costBreakdown = '';
    
    if (daysDiff <= MIN_RENTAL_DAYS) {
      // For 4 days or less, use base price
      rentCost = basePrice;
      costBreakdown = '';
    } else {
      // For more than 4 days: base price + additional days * per day rate
      const additionalDays = daysDiff - MIN_RENTAL_DAYS;
      const additionalCost = additionalDays * perDayRate;
      rentCost = basePrice + additionalCost;
      
      // Show breakdown
      costBreakdown = `
        <div class="d-flex justify-content-between mb-1 small text-muted">
          <span>Base (4 days)</span>
          <span>₹${basePrice.toLocaleString()}</span>
        </div>
        <div class="d-flex justify-content-between mb-1 small text-muted">
          <span>Additional ${additionalDays} day(s) × ₹${Math.round(perDayRate).toLocaleString()}</span>
          <span>₹${Math.round(additionalCost).toLocaleString()}</span>
        </div>
      `;
    }
    
    const totalCost = rentCost + clothData.securityDeposit;
    
    // Update UI
    $rentalDuration.text(`${daysDiff} days`);
    $('#rental-cost-breakdown').html(costBreakdown);
    $rentalCost.text(`₹${Math.round(rentCost).toLocaleString()}`);
    
    $totalPrice.text(Math.round(totalCost).toLocaleString());
    $rentalSummary.show();
    
    // Enable rent button
    $rentButton.prop('disabled', false).html('<i class="bi bi-cart-plus me-2"></i>Rent now - ₹' + Math.round(totalCost).toLocaleString());
    
    // Clear any previous alerts
    $('.alert-danger').remove();
}

function checkAvailability(start, end) {
    const availableBlocks = clothData.availableBlocks;
    
    for (let block of availableBlocks) {
        const blockStart = new Date(block.start_date); // YYYY-MM-DD works in Date constructor
        const blockEnd = new Date(block.end_date);
        
        if (start >= blockStart && end <= blockEnd) {
            return { available: true };
        }
    }
    
    return { 
        available: false, 
        message: 'Selected dates are not within available rental periods' 
    };
}

function checkBlockedDates(start, end) {
    const blockedBlocks = clothData.blockedBlocks;
    
    for (let block of blockedBlocks) {
        const blockStart = new Date(block.start_date);
        const blockEnd = new Date(block.end_date);
        
        // Check if rental period overlaps with blocked period
        if ((start <= blockEnd && end >= blockStart)) {
            return { 
                available: false, 
                message: `Selected dates overlap with blocked period: ${blockStart.toLocaleDateString()} - ${blockEnd.toLocaleDateString()}` 
            };
        }
    }
    
    return { available: true };
}

// Load cart items and check rented status
function loadCartItems() {
    $.ajax({
        url: '/cart/items',
        type: 'GET',
        success: function(response) {
            if (response.cartItems) {
                window.cartItems = response.cartItems;
                checkRentedItems();
            }
        },
        error: function() {
            // If error, assume no items in cart
            window.cartItems = [];
        }
    });
}

// Update all rent buttons for a specific item
function updateAllRentButtons(clothId, isRented) {
    const buttons = $(`.add-to-cart-btn[data-cloth-id="${clothId}"]`);
    
    buttons.each(function() {
        const $btn = $(this);
        
        if (isRented) {
            $btn.text('RENTED')
                .addClass('btn-success')
                .removeClass('btn-warning')
                .prop('disabled', true)
                .attr('title', 'Already in cart');
            // Also disable Buy button for same item
            const $buyBtn = $(`.add-to-cart-buy-btn[data-cloth-id="${clothId}"]`);
            $buyBtn.prop('disabled', true);
        } else {
            $btn.html('<i class="bi bi-cart-plus me-2"></i>RENT NOW')
                .removeClass('btn-success')
                .addClass('btn-warning')
                .prop('disabled', false)
                .removeAttr('title');
            // Re-enable Buy button if not purchased
            const $buyBtn = $(`.add-to-cart-buy-btn[data-cloth-id="${clothId}"]`);
            $buyBtn.prop('disabled', false);
        }
    });
}

// Update all buy buttons for a specific item
function updateAllBuyButtons(clothId, isPurchased) {
    const buttons = $(`.add-to-cart-buy-btn[data-cloth-id="${clothId}"]`);
    
    buttons.each(function() {
        const $btn = $(this);
        
        if (isPurchased) {
            $btn.text('PURCHASED')
                .addClass('btn-success')
                .removeClass('btn-primary')
                .prop('disabled', true)
                .attr('title', 'Already purchased');
            // Also disable Rent button for same item
            const $rentBtn = $(`.add-to-cart-btn[data-cloth-id="${clothId}"]`);
            $rentBtn.prop('disabled', true).text('RENTED');
        } else {
            $btn.html('<i class="bi bi-bag-check me-2"></i>BUY NOW - ₹{{ number_format($cloth->purchase_value) }}')
                .removeClass('btn-success')
                .addClass('btn-primary')
                .prop('disabled', false)
                .removeAttr('title');
            // Re-enable Rent button if not in cart
            const $rentBtn = $(`.add-to-cart-btn[data-cloth-id="${clothId}"]`);
            $rentBtn.prop('disabled', false).html('<i class="bi bi-cart-plus me-2"></i>RENT NOW');
        }
    });
}

// Check which items are already in cart and update buttons
function checkRentedItems() {
    if (!window.cartItems) return;
    
    window.cartItems.forEach(function(item) {
        if (item.purchase_type === 'buy') {
            updateAllBuyButtons(item.cloth_id, true);
        } else {
            updateAllRentButtons(item.cloth_id, true);
        }
    });
}

// Update cart count in header
function updateCartCount(count) {
    const $cartCount = $('#cart-count');
    if ($cartCount.length > 0) {
        $cartCount.text(count);
        if (count > 0) {
            $cartCount.show();
        } else {
            $cartCount.hide();
        }
    }
}

// Show alert message
function showAlert(type, message) {
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    // Remove existing alerts
    $('.alert').remove();
    
    // Add new alert
    $('body').prepend(alertHtml);
    
    // Auto-hide after 3 seconds
    setTimeout(function() {
        $('.alert').fadeOut();
    }, 3000);
}

// Reviews and Questions JavaScript
$(document).ready(function() {
    const clothId = {{ $cloth->id }};
    
    // Star Rating Input
    let selectedRating = 0;
    $('.star-rating-input .bi-star, .star-rating-input .bi-star-fill').on('click', function() {
        selectedRating = parseInt($(this).data('rating'));
        $('#rating').val(selectedRating);
        
        // Update stars display
        $('.star-rating-input .bi-star, .star-rating-input .bi-star-fill').each(function() {
            const starRating = parseInt($(this).data('rating'));
            if (starRating <= selectedRating) {
                $(this).removeClass('bi-star').addClass('bi-star-fill');
            } else {
                $(this).removeClass('bi-star-fill').addClass('bi-star');
            }
        });
        
        $('.rating-text').text(selectedRating + ' out of 5 stars');
    });
    
    // Hover effect for stars
    $('.star-rating-input .bi-star, .star-rating-input .bi-star-fill').on('mouseenter', function() {
        const hoverRating = parseInt($(this).data('rating'));
        $('.star-rating-input .bi-star, .star-rating-input .bi-star-fill').each(function() {
            const starRating = parseInt($(this).data('rating'));
            if (starRating <= hoverRating) {
                $(this).removeClass('bi-star').addClass('bi-star-fill');
            } else {
                $(this).removeClass('bi-star-fill').addClass('bi-star');
            }
        });
    });
    
    $('.star-rating-input').on('mouseleave', function() {
        $('.star-rating-input .bi-star, .star-rating-input .bi-star-fill').each(function() {
            const starRating = parseInt($(this).data('rating'));
            if (starRating <= selectedRating) {
                $(this).removeClass('bi-star').addClass('bi-star-fill');
            } else {
                $(this).removeClass('bi-star-fill').addClass('bi-star');
            }
        });
    });
    
    // Character counter for review
    $('#review_text').on('input', function() {
        const length = $(this).val().length;
        $('#review_char_count').text(length);
    });
    
    // Character counter for question
    $('#question_text').on('input', function() {
        const length = $(this).val().length;
        $('#question_char_count').text(length);
    });
    
    // Review Form Submission
    $('#reviewForm').on('submit', function(e) {
        e.preventDefault();
        
        const rating = $('#rating').val();
        const reviewText = $('#review_text').val();
        
        if (!rating) {
            $('#rating-error').text('Please select a rating').show();
            return;
        }
        
        $('#rating-error').hide();
        
        const formData = {
            _token: $('meta[name="csrf-token"]').attr('content'),
            rating: rating,
            review: reviewText
        };
        
        $.ajax({
            url: '/clothes/' + clothId + '/reviews',
            method: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    showAlert('success', response.message);
                    setTimeout(function() {
                        window.location.reload();
                    }, 1000);
                }
            },
            error: function(xhr) {
                if (xhr.status === 401) {
                    window.location.href = "{{ route('login') }}?redirect=" + encodeURIComponent(window.location.href);
                } else {
                    const error = xhr.responseJSON;
                    if (error && error.errors) {
                        const firstError = Object.values(error.errors)[0];
                        showAlert('danger', Array.isArray(firstError) ? firstError[0] : firstError);
                    } else {
                        showAlert('danger', error?.message || 'Failed to post review. Please try again.');
                    }
                }
            }
        });
    });
    
    // Question Form Submission
    $('#questionForm').on('submit', function(e) {
        e.preventDefault();
        
        const questionText = $('#question_text').val().trim();
        
        if (!questionText) {
            $('#question-error').text('Please enter a question').show();
            return;
        }
        
        $('#question-error').hide();
        
        const formData = {
            _token: $('meta[name="csrf-token"]').attr('content'),
            question: questionText
        };
        
        $.ajax({
            url: '/clothes/' + clothId + '/questions',
            method: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    showAlert('success', response.message);
                    setTimeout(function() {
                        window.location.reload();
                    }, 1000);
                }
            },
            error: function(xhr) {
                if (xhr.status === 401) {
                    window.location.href = "{{ route('login') }}?redirect=" + encodeURIComponent(window.location.href);
                } else {
                    const error = xhr.responseJSON;
                    if (error && error.errors) {
                        const firstError = Object.values(error.errors)[0];
                        showAlert('danger', Array.isArray(firstError) ? firstError[0] : firstError);
                    } else {
                        showAlert('danger', error?.message || 'Failed to post question. Please try again.');
                    }
                }
            }
        });
    });
    
    // Delete Review
    $(document).on('click', '.delete-review-btn', function() {
        if (!confirm('Are you sure you want to delete this review?')) {
            return;
        }
        
        const reviewId = $(this).data('review-id');
        
        $.ajax({
            url: '/reviews/' + reviewId,
            method: 'DELETE',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    showAlert('success', response.message);
                    setTimeout(function() {
                        window.location.reload();
                    }, 1000);
                }
            },
            error: function(xhr) {
                const error = xhr.responseJSON;
                showAlert('danger', error?.message || 'Failed to delete review.');
            }
        });
    });
    
    // Delete Question
    $(document).on('click', '.delete-question-btn', function() {
        if (!confirm('Are you sure you want to delete this question?')) {
            return;
        }
        
        const questionId = $(this).data('question-id');
        
        $.ajax({
            url: '/questions/' + questionId,
            method: 'DELETE',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    showAlert('success', response.message);
                    setTimeout(function() {
                        window.location.reload();
                    }, 1000);
                }
            },
            error: function(xhr) {
                const error = xhr.responseJSON;
                showAlert('danger', error?.message || 'Failed to delete question.');
            }
        });
    });
    
    // Show Answer Form
    $(document).on('click', '.answer-question-btn', function() {
        $(this).closest('.question-item').find('.answer-form').show();
        $(this).hide();
    });
    
    // Cancel Answer
    $(document).on('click', '.cancel-answer-btn', function() {
        $(this).closest('.question-item').find('.answer-form').hide();
        $(this).closest('.question-item').find('.answer-question-btn').show();
    });
    
    // Submit Answer
    $(document).on('submit', '.answer-question-form', function(e) {
        e.preventDefault();
        
        const questionId = $(this).closest('.question-item').data('question-id');
        const answerText = $(this).find('textarea[name="answer"]').val().trim();
        
        if (!answerText) {
            showAlert('danger', 'Please enter an answer');
            return;
        }
        
        $.ajax({
            url: '/questions/' + questionId + '/answer',
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                answer: answerText
            },
            success: function(response) {
                if (response.success) {
                    showAlert('success', response.message);
                    setTimeout(function() {
                        window.location.reload();
                    }, 1000);
                }
            },
            error: function(xhr) {
                const error = xhr.responseJSON;
                showAlert('danger', error?.message || 'Failed to post answer.');
            }
        });
    });
    
    @if(isset($userReview) && $userReview)
    // Load existing user review
    selectedRating = {{ $userReview->rating }};
    $('#rating').val(selectedRating);
    $('#review_text').val('{{ addslashes($userReview->review) }}');
    $('#review_char_count').text($('#review_text').val().length);
    
    $('.star-rating-input .bi-star, .star-rating-input .bi-star-fill').each(function() {
        const starRating = parseInt($(this).data('rating'));
        if (starRating <= selectedRating) {
            $(this).removeClass('bi-star').addClass('bi-star-fill');
        }
    });
    $('.rating-text').text(selectedRating + ' out of 5 stars');
    @endif

    // Image Lightbox
    $('#activeProductImage').on('click', function() {
        const src = $(this).attr('src');
        $('#lightboxImage').attr('src', src);
        const modal = new bootstrap.Modal(document.getElementById('imageLightboxModal'));
        modal.show();
    });
});
</script>
@endsection 