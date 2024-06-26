<?php

namespace App\Models\Payment;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class TempTransaction extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $connection = 'pgsql_masters';

    /**
     * | Save temp details 
     */
    public function tempTransaction($req)
    {
        $mTempTransaction = new TempTransaction();
        $mTempTransaction->create($req);
    }

    public function transactionDtl($date, $ulbId)
    {
        return TempTransaction::select('temp_transactions.*', 'users.*', 'ulb_ward_masters.id as ward_id', )
            ->leftjoin('users', 'users.id', 'temp_transactions.user_id')
            // ->leftjoin("ulb_ward_masters", "ulb_ward_masters.ward_name", "temp_transactions.ward_no")
            ->leftJoin("ulb_ward_masters", DB::raw('CAST(ulb_ward_masters.ward_name AS TEXT)'), '=', DB::raw('CAST(temp_transactions.ward_no AS TEXT)'))
            ->where('tran_date', $date)
            ->where('temp_transactions.status', 1)
            ->where('temp_transactions.ulb_id', $ulbId)
            ->orderByDesc('temp_transactions.id');
    }

    public function transactionList($date, $userId, $ulbId)
    {
        return TempTransaction::select(
            'temp_transactions.id',
            'transaction_no as tran_no',
            'payment_mode',
            'cheque_dd_no',
            'bank_name',
            'amount',
            'module_id',
            'workflow_id',
            'ward_no as ward_name',
            'application_no',
            DB::raw("TO_CHAR(tran_date, 'DD-MM-YYYY') as tran_date"),
            'name as user_name',
            'users.id as tc_id'
        )
            ->join('users', 'users.id', 'temp_transactions.user_id')
            ->where('payment_mode', '!=', 'ONLINE')
            ->where('tran_date', $date)
            ->where('temp_transactions.status', 1)
            ->where('user_id', $userId)
            ->where('temp_transactions.ulb_id', $ulbId)
            ->get();
    }
}
