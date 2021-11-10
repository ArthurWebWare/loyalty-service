<?php

namespace App\Http\Controllers\Api\v1;

use App\Mail\LoyaltyPointsReceived;
use App\Models\LoyaltyAccount;
use App\Models\LoyaltyPointsTransaction;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\LoyaltyPointsRule;
use Validator;

class TransactionsController extends Controller
{
    public function deposit($type,$id, Request $request)
    {
        $data = $request->all();

        $validator = Validator::make($data, [
            'points_rule' => 'required|exists:loyalty_points_rule,id',
            'points_amount' => 'required|numeric',
            'description' => 'string|max:255',
            'payment_id' => 'required|string',
            'payment_amount' => 'required|numeric',
            'payment_time' => 'int'
        ]);
        
        if($validator->fails()) {
            return response()->json([
                'message' => 'Create transaction failed',
                'errors' => $validator->errors()->all(),
            ],400);
        }
        
        if (in_array($type,['phone','card','email']) && !empty($id)) {
            if ($account = LoyaltyAccount::where($type, '=', $id)->first()) {
                if ($account->active) {
                    $transaction =  LoyaltyPointsTransaction::performPaymentLoyaltyPoints(
                        $account->id, 
                        $data['points_rule'], 
                        $data['description']?$data['description']:'', 
                        $data['payment_id'], 
                        $data['payment_amount'], 
                        $data['payment_time']
                    );

                    if (!empty($account->email) && $account->email_notification) {
                        Mail::to($account)->send(new LoyaltyPointsReceived($transaction->points_amount, $account->getBalance()));
                    }
                    if (!empty($account->phone) && $account->phone_notification) {
                        // instead SMS component
                    }
                     return response()->json(['message'=> 'Success','data' => $transaction], 200);

                } else {
                    return response()->json(['errors'=> ['Account is not active']], 400);
                }
            } else {
                return response()->json(['errors' => ['Account is not found']], 400);
            }
        } else {
            return response()->json(['errors' => ['Wrong account parameters']], 400);
        }
    }

    public function cancel(Request $request)
    {
        $data = $request->all();

        $validator = Validator::make($data, [
            'transaction_id' => 'required|exists:loyalty_points_transaction,id',
            'cancellation_reason' => 'required|string|max:255',
        ]);
        
        if($validator->fails()) {
            return response()->json([
                'message' => 'Cancel transaction failed',
                'errors' => $validator->errors()->all(),
            ],400);
        }

        if ($transaction = LoyaltyPointsTransaction::where('id', '=', $data['transaction_id'])->where('canceled', '=', 0)->first()) {
            $transaction->canceled = time();
            $transaction->cancellation_reason = $data['cancellation_reason'];
            
            if($transaction->save()){
                return response()->json(['message' => 'Success'], 200);
            }

        } else {
            return response()->json(['errors' => ['Transaction is not found']], 400);
        }
    }

    public function withdraw($type,$id,Request $request)
    {
        $data = $request->all();

        $validator = Validator::make($data, [
            'description' => 'required|string|max:255',
            'points_amount' => 'required|numeric',
        ]);
        
        if($validator->fails()) {
            return response()->json([
                'message' => 'Cancel transaction failed',
                'errors' => $validator->errors()->all(),
            ],400);
        }

        if (in_array($type,['phone','card','email']) && !empty($id)) {
            if ($account = LoyaltyAccount::where($type, '=', $id)->first()) {
                if ($account->active) {
                    
                    if ($data['points_amount'] <= 0) {
                        return response()->json(['errors' => ['Wrong loyalty points amount: ' . $data['points_amount']]], 400);
                    }
                    if ($account->getBalance() < $data['points_amount']) {
                        return response()->json(['errors' => 'Insufficient funds','data' => ['points_amount'=>$data['points_amount']]], 400);
                    }

                    $transaction = LoyaltyPointsTransaction::withdrawLoyaltyPoints($account->id, $data['points_amount'], $data['description']);

                    return response()->json(['message'=> 'Success', 'data' => $transaction]);
                } else {
                    return response()->json(['errors' => ['Account is not active: ' . $type . ' ' . $id]], 400);
                }
            } else {
                return response()->json(['errors' => ['Account is not found']], 400);
            }
        } else {
            return response()->json(['errors' => ['Wrong account parameters']], 400);
        }
    }
}
