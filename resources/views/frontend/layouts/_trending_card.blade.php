@php
    $photo = !empty($product->photo) ? explode(',', $product->photo) : [];
    $mainPhoto = $photo[0] ?? null;
    $hoverPhoto = $photo[1] ?? null;

    $sizeData = null;
    $productPrice = 0;
    if(!empty($product->size)){
        $decoded = json_decode($product->size, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded) && isset($decoded['price']) && is_array($decoded['price']) && count($decoded['price']) > 0) {
            $sizeData = $decoded;
            $productPrice = floatval($decoded['price'][0] ?? 0);
        }
    }
    if(!$productPrice && isset($product->price) && is_numeric($product->price)){
        $productPrice = floatval($product->price);
    }
    $discountVal = isset($product->discount) && is_numeric($product->discount) ? floatval($product->discount) : 0;
    $afterDiscount = ($discountVal > 0 && $productPrice > 0) ? ($productPrice - (($productPrice * $discountVal) / 100)) : $productPrice;
@endphp

<div class="custom-product-card">
    <div class="card-media-wrap">
        {{-- Left Side Badges (Temporarily hidden) --}}
        {{--
        <div class="card-left-badges">
            @if($product->condition == 'hot' || $product->is_featured == 1)
                <span class="card-badge-tag badge-hot"><i class="fa fa-fire"></i> HOT</span>
            @elseif($product->condition == 'new')
                <span class="card-badge-tag badge-new">NEW</span>
            @elseif(!empty($product->condition) && $product->condition != 'default')
                <span class="card-badge-tag badge-hot">{{ strtoupper($product->condition) }}</span>
            @else
                <span class="card-badge-tag badge-hot"><i class="fa fa-fire"></i> HOT</span>
            @endif

            @if($discountVal > 0)
                <span class="card-badge-tag badge-discount">{{ round($discountVal) }}% OFF</span>
            @endif
        </div>
        --}}

        {{-- Floating Action Buttons (Temporarily hidden) --}}
        {{--
        <div class="card-action-buttons">
            <a href="{{ route('add-to-wishlist', $product->slug) }}" class="card-btn-action" title="Add to Wishlist">
                <i class="ti-heart"></i>
            </a>
        </div>
        --}}

        {{-- Product Image --}}
        <a href="{{ route('product-detail', $product->slug) }}" class="card-img-link">
            @if($mainPhoto)
                <img class="card-default-img" src="{{ asset('public/'.$mainPhoto) }}" alt="{{ $product->title ?: $product->product_code }}" loading="lazy">
                @if($hoverPhoto)
                    <img class="card-hover-img" src="{{ asset('public/'.$hoverPhoto) }}" alt="{{ $product->title ?: $product->product_code }}" loading="lazy">
                @endif
            @else
                <img class="card-default-img" src="https://via.placeholder.com/400x400?text=No+Image" alt="{{ $product->title ?: $product->product_code }}">
            @endif
        </a>
    </div>

    <div class="card-content-wrap">
        {{-- Top Meta: Category on Left, Price on Right (replacing Rating) --}}
        <div class="card-meta-line">
            <span class="card-cat-name">{{ $product->cat_info->title ?? 'Product' }}</span>
            <div class="card-top-price">
                <span class="price-val">₹{{ number_format($afterDiscount, 2) }}</span>
                @if($discountVal > 0 && $productPrice > $afterDiscount)
                    <del class="price-old">₹{{ number_format($productPrice, 2) }}</del>
                @endif
            </div>
        </div>

        {{-- Product Title --}}
        <h4 class="card-item-title">
            <a href="{{ route('product-detail', $product->slug) }}" title="{{ $product->title ?: $product->product_code }}">
                {{ $product->title ?: $product->product_code }}
            </a>
        </h4>

        {{-- CTA Button --}}
        <div class="card-cta-container">
            <a href="{{ route('product-detail', $product->slug) }}" class="btn-card-details">
                <span>View Details</span>
                <i class="ti-arrow-right"></i>
            </a>
        </div>
    </div>
</div>
