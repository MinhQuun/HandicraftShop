@extends('layouts.staff')
@section('title','Hộp thư ý kiến')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/staff-reviews.css') }}">
@endpush

@section('content')
<section class="page-header">
    <span class="kicker">Nhân viên</span>
    <h1 class="title">Hộp thư ý kiến</h1>
    <p class="muted">Xem, phân loại trạng thái và phản hồi ý kiến từ khách hàng.</p>
</section>

<div id="flash"
    data-success="{{ session('success') }}"
    data-error="{{ session('error') }}"
    data-info="{{ session('info') }}"
    data-warning="{{ session('warning') }}">
</div>

<div class="card reviews-filter mb-3">
    <div class="card-body">
        <form class="row g-2 align-items-center" method="get" action="{{ route('staff.reviews.index') }}">
        <div class="col-lg-6">
            <input type="text" name="q" value="{{ $q ?? request('q') }}" class="form-control"
                placeholder="Tìm theo họ tên, email, nội dung">
        </div>
        <div class="col-lg-3">
            @php $st = $status ?? request('status'); @endphp
            <select name="status" class="form-select">
            <option value="">-- Tất cả trạng thái --</option>
            <option value="NEW"     {{ $st==='NEW' ? 'selected' : '' }}>Mới</option>
            <option value="READ"    {{ $st==='READ' ? 'selected' : '' }}>Đã đọc</option>
            <option value="REPLIED" {{ $st==='REPLIED' ? 'selected' : '' }}>Đã phản hồi</option>
            </select>
        </div>
        <div class="col-lg-3 d-flex gap-2 justify-content-lg-end">
            <button class="btn btn-outline-primary">Lọc</button>
            <a href="{{ route('staff.reviews.index') }}" class="btn btn-outline-secondary">Xoá lọc</a>
        </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h5 class="m-0">Danh sách ý kiến</h5>
        @if(($badges['new'] ?? 0) > 0)
        <span class="badge st-new">Mới: {{ $badges['new'] }}</span>
        @endif
    </div>

    <div class="table-responsive">
        <table class="table align-middle mb-0 table-hover reviews-table">
        <thead>
        <tr>
            <th style="width:84px;">Mã</th>
            <th style="min-width:220px;">Họ tên</th>
            <th style="width:22%;">Email</th>
            <th>Nội dung</th>
            <th style="width:12%;">Trạng thái</th>
            <th style="width:150px;">Thời gian</th>
            <th style="width:180px;" class="text-end">Thao tác</th>
        </tr>
        </thead>
        <tbody>
        @forelse($items as $it)
            @php
            $id      = $it->ID;
            $name    = $it->NAME;
            $email   = $it->EMAIL;
            $msg     = $it->MESSAGE;
            $status  = $it->STATUS ?? 'NEW';
            $created = \Illuminate\Support\Carbon::parse($it->CREATED_AT)->format('d/m/Y H:i');
            $badgeClass = $status === 'NEW' ? 'st-new' : ($status === 'REPLIED' ? 'st-replied' : 'st-read');
            $statusText = $status === 'NEW' ? 'Mới' : ($status === 'REPLIED' ? 'Đã phản hồi' : 'Đã đọc');
            @endphp
            <tr>
            <td>{{ $id }}</td>
            <td class="text-truncate" title="{{ $name }}">{{ $name }}</td>
            <td class="text-truncate" title="{{ $email }}">{{ $email }}</td>
            <td class="text-truncate" title="{{ $msg }}">{{ \Illuminate\Support\Str::limit($msg, 80) }}</td>
            <td><span class="badge {{ $badgeClass }}">{{ $statusText }}</span></td>
            <td>{{ $created }}</td>
            <td class="text-end">
                <button type="button"
                    class="btn btn-sm btn-primary-soft me-1 btn-view"
                    data-bs-toggle="modal" data-bs-target="#modalView"
                    data-id="{{ $id }}"
                    data-name="{{ $name }}"
                    data-email="{{ $email }}"
                    data-message="{{ $msg }}"
                    data-status="{{ $status }}"
                    data-time="{{ $created }}"
                    title="Xem chi tiết">
                    <i class="bi bi-eye"></i>
                </button>

                <form action="{{ route('staff.reviews.update', $id) }}" method="post" class="d-inline">
                @csrf @method('put')
                <input type="hidden" name="action" value="{{ $status === 'READ' ? 'mark_unread' : 'mark_read' }}">
                <button class="btn btn-sm btn-primary-soft me-1" title="{{ $status==='READ' ? 'Đánh dấu: Mới' : 'Đánh dấu: Đã đọc' }}">
                    <i class="bi {{ $status==='READ' ? 'bi-envelope-open' : 'bi-envelope' }}"></i>
                </button>
                </form>

                <form action="{{ route('staff.reviews.destroy', $id) }}" method="post" class="d-inline form-delete">
                @csrf @method('delete')
                <button class="btn btn-sm btn-danger-soft" title="Xoá">
                    <i class="bi bi-trash"></i>
                </button>
                </form>
            </td>
            </tr>
        @empty
            <tr><td colspan="7" class="text-center text-muted py-4">Chưa có ý kiến</td></tr>
        @endforelse
        </tbody>
        </table>
    </div>

    @if ($items->lastPage() > 1)
        <nav aria-label="Page navigation" class="mt-4">
        <ul class="pagination justify-content-center">
            @if ($items->currentPage() > 1)
            <li class="page-item"><a class="page-link" href="{{ $items->previousPageUrl() }}">Trước</a></li>
            @endif
            @for ($i = 1; $i <= $items->lastPage(); $i++)
            <li class="page-item {{ $i === $items->currentPage() ? 'active' : '' }}">
                <a class="page-link" href="{{ $items->url($i) }}">{{ $i }}</a>
            </li>
            @endfor
            @if ($items->currentPage() < $items->lastPage())
            <li class="page-item"><a class="page-link" href="{{ $items->nextPageUrl() }}">Sau</a></li>
            @endif
        </ul>
        </nav>
    @endif
</div>

<div class="modal fade" id="modalView" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Ý kiến của khách hàng</h5>
            <button class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
            <dl class="row g-2 mb-3">
                <dt class="col-sm-3">Họ tên</dt><dd class="col-sm-9" id="v_name">—</dd>
                <dt class="col-sm-3">Email</dt><dd class="col-sm-9" id="v_email">—</dd>
                <dt class="col-sm-3">Thời gian</dt><dd class="col-sm-9" id="v_time">—</dd>
                <dt class="col-sm-3">Nội dung</dt><dd class="col-sm-9"><pre id="v_message" class="mb-0"></pre></dd>
            </dl>

            {{-- Khối phản hồi đẹp hơn --}}
            <form id="formReply" method="post"
                data-action-template="{{ route('staff.reviews.update', ':id') }}"
                class="reply-box">
                @csrf @method('put')
                <input type="hidden" name="action" value="save_reply">

                <label for="reply_message" class="form-label mb-2">Phản hồi cho khách (tuỳ chọn)</label>
                <div class="input-group reply-input">
                    <span class="input-group-text"><i class="bi bi-chat-dots"></i></span>
                    <textarea id="reply_message" name="reply_message"
                            class="form-control reply-textarea"
                            maxlength="5000" rows="4"
                            placeholder="Nhập nội dung phản hồi... (Ctrl + Enter để gửi)"></textarea>
                </div>
                <div class="d-flex justify-content-between mt-1">
                    <small class="text-muted" id="replyCounter">0/5000</small>
                </div>
            </form>
        </div>

        <div class="modal-footer">
            <button class="btn btn-primary" form="formReply">
                <i class="bi bi-reply"></i> Gửi phản hồi / Lưu ghi chú
            </button>
            <button class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
        </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('js/staff-reviews.js') }}"></script>
@endpush
