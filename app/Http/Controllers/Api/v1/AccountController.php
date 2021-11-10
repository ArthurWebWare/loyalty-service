<?php

namespace App\Http\Controllers\Api\v1;

use App\Models\LoyaltyAccount;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Validator;

class AccountController extends Controller
{   
    public function index(Request $request)
    {   
        $perPage = $request->get('per_page', 10);
        $perPage = $perPage > 1000 ? 1000 : $perPage;
        $numPage = $request->get('page', 1);

        $offset = ($numPage * $perPage) - $perPage;

        $users = DB::table((new LoyaltyAccount)->getTable())
        ->orderBy('id')
        ->limit($perPage)
        ->offset($offset)
        ->get();

        $count = $users->count();
        if(!$count){
            return response()->json(['message' => 'Accounts Not Found'], 200);
        }

        return response()->json(['message' => 'Success','data' => $users], 200);
    }

    public function create(Request $request)
    {   
        $validateRules = [
            'phone' => 'required|string|max:160|unique:loyalty_account',
            'email' => 'required|string|email|max:255|unique:loyalty_account',
            'card' => 'required|string|max:255|min:4|unique:loyalty_account',
        ];
        $validator = Validator::make($request->all(),$validateRules);

        if($validator->fails()){
            return response()->json([
                'message' => 'Create account failed',
                'errors' => $validator->errors()->all(),
            ],401);
        }
        $insertData = [
            'phone' => $request->phone,
            'email' => $request->email,
            'card' => $request->card,
        ];

        if($account = LoyaltyAccount::create($insertData)) {
            return response()->json([
                'message' => 'Success',
                'data' => [$account],
            ],201);
        } else {
            return response()->json([
                'errors' => ['Create account failed: Bad Request']
            ],400);
        }
    }

    public function view($type,$id)
    {   
        if(in_array($type,['phone','card','email']) && !empty($id)) {
            if($account = LoyaltyAccount::where($type, '=', $id)->first()) {
                return response()->json(['message'=>'Success','data'=>$account],200);
            } else {
                return response()->json(['errors'=> ['Account not found']],401);
            }
        }
    }

    public function activate($type, $id)
    {
        if (in_array($type,['phone','card','email']) && !empty($id)) {
            if ($account = LoyaltyAccount::where($type, '=', $id)->first()) {
                if (!$account->active) {
                    $account->active = true;
                    $account->save();
                    $account->notify('Account restored');

                    return response()->json(['message' => 'Success','data'=>$account],200);
                } else {
                    return response()->json(['message' => 'Account is already active','data'=>$account], 200);
                }
            } else {
                return response()->json(['errors'=> ['Account is not found']], 400);
            }
        } else {
            return response()->json(['errors'=> ['Bad credentials: type or id is empty']], 400);
        }
    }

    public function deactivate($type, $id)
    {
        if (in_array($type,['phone','card','email']) && !empty($id)) {
            if ($account = LoyaltyAccount::where($type, '=', $id)->first()) {
                if ($account->active) {
                    $account->active = false;
                    $account->save();
                    $account->notify('Account banned');

                } else {
                    return response()->json(['message' => 'Account is already banned','data'=>$account], 200);
                }
            } else {
                return response()->json(['errors'=> ['Account is not found']], 400);
            }
        } else {
            return response()->json(['errors'=> ['Bad credentials: type or id is empty']], 400);
        }

        return response()->json(['message' => 'Success','data'=>$account],200);
    }

    public function balance($type, $id)
    {
        if (in_array($type,['phone','card','email']) && !empty($id)) {
            if ($account = LoyaltyAccount::where($type, '=', $id)->first()) {
                return response()->json([
                    'message' => 'Success',
                    'data' => $account,
                    'balance' => $account->getBalance()], 200);
            } else {
                return response()->json(['errors' => ['Account is not found: type or id is wrong']], 400);
            }
        } else {
            return response()->json(['errors'=> ['Bad credentials: type or id is empty']], 400);
        }
    }
}
