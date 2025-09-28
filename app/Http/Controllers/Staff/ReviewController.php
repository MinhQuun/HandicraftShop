<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\ContactReplyMail;
use Illuminate\Pagination\LengthAwarePaginator;

class ReviewController extends Controller
{
    protected string $table = 'LIENHE';

    public function index(Request $req)
    {
        if (!DB::getSchemaBuilder()->hasTable($this->table)) {
            $items = new LengthAwarePaginator([], 0, 12);
            return view('staff.reviews', [
                'items'  => $items,
                'badges' => ['new' => 0],
            ])->with('warning','Chưa có bảng LIENHE. Vui lòng tạo bảng trước.');
        }

        $q = trim((string) $req->get('q', ''));
        $status = strtoupper(trim((string) $req->get('status', '')));

        $query = DB::table($this->table);

        // --- Search: hỗ trợ nhiều từ (tên riêng, họ tên đầy đủ, email, nội dung)
        if ($q !== '') {
            $tokens = preg_split('/\s+/', $q, -1, PREG_SPLIT_NO_EMPTY);
            foreach ($tokens as $t) {
                $like = '%' . $t . '%';
                $query->where(function ($w) use ($like) {
                    $w->where('NAME', 'like', $like)
                        ->orWhere('EMAIL', 'like', $like)
                        ->orWhere('MESSAGE', 'like', $like);
                });
            }
        }

        // --- Filter status: '' hoặc 'ALL' = không lọc
        $allowed = ['NEW','READ','REPLIED'];
        if ($status !== '' && $status !== 'ALL' && in_array($status, $allowed, true)) {
            $query->where('STATUS', $status);
        }

        $items = $query->orderByDesc('ID')->paginate(12)->withQueryString();

        $badges = [
            'new' => (int) DB::table($this->table)->where('STATUS','NEW')->count(),
        ];

        return view('staff.reviews', [
            'items'  => $items,
            'q'      => $q,
            'status' => $status,
            'badges' => $badges,
        ]);
    }

    public function update($id, Request $req)
    {
        if (!DB::getSchemaBuilder()->hasTable($this->table)) {
            return back()->with('error','Chưa có bảng LIENHE.');
        }

        $action = $req->input('action');

        if ($action === 'mark_read') {
            DB::table($this->table)->where('ID',$id)->update([
                'STATUS'     => 'READ',
                'UPDATED_AT' => Carbon::now(),
            ]);
            return back()->with('success','Đã đánh dấu: ĐÃ ĐỌC.');
        }

        if ($action === 'mark_unread') {
            DB::table($this->table)->where('ID',$id)->update([
                'STATUS'     => 'NEW',
                'UPDATED_AT' => Carbon::now(),
            ]);
            return back()->with('success','Đã đánh dấu: MỚI.');
        }

        if ($action === 'save_reply') {
            $msg = $req->validate([
                'reply_message' => ['nullable','string','max:5000'],
            ])['reply_message'] ?? null;

            DB::table($this->table)->where('ID',$id)->update([
                'REPLY_MESSAGE' => $msg,
                'STATUS'        => $msg ? 'REPLIED' : DB::raw('STATUS'),
                'REPLIED_AT'    => $msg ? Carbon::now() : null,
                'REPLY_BY'      => $req->user()?->id,
                'UPDATED_AT'    => Carbon::now(),
            ]);

            $it = DB::table($this->table)->where('ID', $id)
                    ->first(['NAME','EMAIL','MESSAGE']);
            $sent = false;

            if ($msg && $it && filter_var($it->EMAIL, FILTER_VALIDATE_EMAIL)) {
                try {
                    Mail::to($it->EMAIL)->send(
                        new ContactReplyMail(
                            name: $it->NAME ?? 'bạn',
                            originalMessage: $it->MESSAGE ?? '',
                            replyMessage: $msg,
                            repliedAt: Carbon::now()
                        )
                    );
                    $sent = true;
                } catch (\Throwable $e) {
                    // \Log::error($e->getMessage());
                }
            }

            return back()->with(
                'success',
                $msg
                    ? ('Đã lưu phản hồi.' . ($sent ? ' Đã gửi email cho khách.' : ' (Email chưa gửi được – kiểm tra cấu hình mail)'))
                    : 'Đã lưu.'
            );
        }

        return back()->with('info','Không có hành động nào được thực hiện.');
    }

    public function destroy($id)
    {
        if (!DB::getSchemaBuilder()->hasTable($this->table)) {
            return back()->with('error','Chưa có bảng LIENHE.');
        }
        DB::table($this->table)->where('ID',$id)->delete();
        return back()->with('success','Đã xoá ý kiến.');
    }
}