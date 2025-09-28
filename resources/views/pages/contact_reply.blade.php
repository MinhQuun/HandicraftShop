<p>Chào {{ $name }},</p>

<p>Chúng tôi đã nhận được ý kiến của bạn:</p>
<blockquote style="margin:0 0 12px;padding:8px 12px;border-left:3px solid #ddd;">
    {{ $originalMessage }}
</blockquote>

@if($replyMessage)
    <p>Phản hồi của chúng tôi ({{ $repliedAt->format('d/m/Y H:i') }}):</p>
    <div style="background:#f7f9fb;padding:12px;border:1px solid #e3eaf0;border-radius:8px;">
        {{ $replyMessage }}
    </div>
@else
    <p>Chúng tôi đã ghi nhận ý kiến và sẽ phản hồi sớm nhất.</p>
@endif

<p style="margin-top:16px">Trân trọng,<br>Handicraft Shop</p>
