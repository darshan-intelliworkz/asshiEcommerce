<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Settings;
use App\User;
use App\Rules\MatchOldPassword;
use Hash;
use Carbon\Carbon;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;

class AdminController extends Controller
{
    private $aspEncryptedSecretKey;
    private $aspId;

    public function __construct(Request $request)
    {
        $this->aspEncryptedSecretKey = 'B60KRxgyOk2KWUEudvfzxd2sxuwolUZpRU8ZSHmjl6kTfXoStUO/K6koDFYWwsh2br4aS30FXQD/fkaehS7WCknGrKWH6OMrKFhy8YtW4C/3DjspcqMWmAVXC67iqgzbbgPQdOx0CjXjEg2yv6cBQx5sxY3DZxTxCFb5zFzaufsk0zkaTHjEZUhb581v8KWjeW0XHaxcPuCEv+C1XiqT0ldrjsBBawiFr9xvpz7Qxxbx3T/mDf37Ai0fscJ+CqlaOoGUza0LCM9449RWVFqYW1FiKefU2wzDczix1+2IPmcBeZhNhudF3Rks/u8okburUPZhso6oij2LM1FbHqoOpg==';
        $this->aspId = '1763872424';
    }

    public function testGST(Request $request, $gstin = null){
        $gstin = '24AAAPT5917F1ZS';
        $sessionData = $this->getKeyAndSession();
        $encryptedAspSecret = $this->aesEncrypt(
            $sessionData['aspSecret'],   // plain 32-char ASP secret
            $sessionData['AspEK']        // decrypted AES session key
        );

       $txnId = (string) Str::uuid();
       $response = $this->getGstApisearch($txnId, $gstin, $sessionData, $encryptedAspSecret);
       $response = $response->json();
       return $response;
    }

    public function getGstApisearch($txnId, $gstin, $sessionData, $encryptedAspSecret)
    {
        $production_url = 'https://gstapi.charteredinfo.com/commonapi/v1.1';
        $response = Http::withHeaders([
                'aspid' => $this->aspId,
                'session-id' => $sessionData['session-id'],
                'asp-secret' => $encryptedAspSecret,
                'txn' => $txnId,
                'gstin' => $gstin,
                'ip-usr' => request()->ip(),
                'Content-Type' => 'application/json; charset=utf-8',
            ])->get($production_url . '/search', [
                'action' => 'TP',
            ]);
        return $response;
    }

    public function getKeyAndSession(): array
    {
        $url = 'https://gstapi.charteredinfo.com/aspapi/v1.0/getKey';

        $txnId = (string) Str::uuid();
        $timestamp = now()->format('dmYHis') . strtoupper(Str::random(6));
        $signedContent = $this->signContent($this->aspId, $timestamp);
        $response = Http::withHeaders([
            'aspid' => $this->aspId,
            'txn' => $txnId,
            'Content-Type' => 'application/json; charset=utf-8',
            'ip-usr' => request()->ip(),
        ])->post($url, [
            'timestamp' => $timestamp,
            'signed_content' => $signedContent,
        ]);
  
        if ($response->failed()) {
            throw new \Exception('Failed to getKey: ' . $response->body());
        }

        $data = $response->json();
        
        if (isset($data['session_id'], $data['enc_key'])) {
            $aspSecret = $this->aesDecryptAspsecrate($this->aspEncryptedSecretKey);
            $decryptedAspEK = $this->aesDecryptAspEK($data['enc_key'], $aspSecret);
        
            return [
                'session-id' => $data['session_id'],
                'AspEK' => $decryptedAspEK,
                'aspSecret' => $aspSecret,
            ];
        }
    throw new \Exception('Invalid getKey response: ' . json_encode($data));
    }

    private function signContent($aspId, $timestamp)
    {
        $dataToSign = $aspId . $timestamp;

        $pfxPath = storage_path('app/certs/ASP_Certificate_New/AspDsc.pfx');
        $pfxPassword = 'taxpro1234';

        // Read the PFX file
        $pfxContent = file_get_contents($pfxPath);    
        if (!$pfxContent) {
            throw new \Exception("Unable to read DSC file: $pfxPath");
        }
        // Extract the private key
        if (!openssl_pkcs12_read($pfxContent, $certs, $pfxPassword)) {
            throw new \Exception("Invalid DSC password or certificate file");
        }

        $privateKey = openssl_pkey_get_private($certs['pkey']);
        if (!$privateKey) {
            throw new \Exception("Failed to load private key from DSC");
        }

        // Sign the content using SHA256 with RSA
        openssl_sign($dataToSign, $signature, $privateKey, OPENSSL_ALGO_SHA256);
        openssl_free_key($privateKey);
        // Return Base64 encoded signature
        return base64_encode($signature);
    }

    public function aesDecryptAspsecrate(string $encryptedData): string
    {
        $pfxFile = storage_path('app/certs/ASP_Certificate_New/AspDsc.pfx');
        $pfxPassword = 'taxpro1234'; // ensure correct

        $pkcs12 = file_get_contents($pfxFile);
        if (!openssl_pkcs12_read($pkcs12, $certs, $pfxPassword)) {
            throw new \Exception('Unable to read PFX file or invalid password.');
        }

        $privateKey = $certs['pkey'];
        if (!$privateKey) {
            throw new \Exception('Private key not found inside PFX.');
        }

        // --- Step 1: Decode Base64 properly ---
        $binaryEncryptedData = base64_decode(str_replace(["\r", "\n", ' '], '', $encryptedData));

        // --- Step 2: Try PKCS1 padding (default for CharteredInfo) ---
        $decryptedData = '';
        $success = openssl_private_decrypt(
            $binaryEncryptedData,
            $decryptedData,
            $privateKey,
            OPENSSL_PKCS1_PADDING
        );

        if (!$success) {
            while ($msg = openssl_error_string()) {
                \Log::error('OpenSSL Error: ' . $msg);
            }
            throw new \Exception('RSA Decryption of AspSecretKey failed.');
        }

        $decryptedData = trim($decryptedData);

        if (strlen($decryptedData) !== 32) {
            \Log::warning('Unexpected AspSecretKey length: ' . strlen($decryptedData) . ' Value: ' . bin2hex($decryptedData));
        }
        return $decryptedData;
    }

    public function aesDecryptAspEK(string $encryptedDataBase64, string $key): string
    {
        // 1. Decode the Base64 Encrypted Data
        $encryptedData = base64_decode($encryptedDataBase64);
        $cipher = 'AES-256-ECB';
        // 2. Decrypt the data
        // OPENSSL_RAW_DATA: assumes raw binary data
        // OPENSSL_ZERO_PADDING: disables built-in padding, allowing manual PKCS7 removal
        $decrypted = openssl_decrypt(
            $encryptedData,
            $cipher,
            $key, // Use the raw ASP Secret as the key
            OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING
        );

        if ($decrypted === false) {
            throw new Exception("AES decryption of AspEK failed.");
        }

        // 3. Remove PKCS7 Padding
        return $this->stripPkcs7Padding($decrypted);
    }

    private function stripPkcs7Padding(string $data): string
    {
        $pad = ord(substr($data, -1));
        if ($pad < 1 || $pad > 16) {
            return $data;
        }
        return substr($data, 0, -$pad);
    }

    public function aesEncrypt(string $data, string $key): string
    {
        $blockSize = 16;
        $pad = $blockSize - (strlen($data) % $blockSize);
        $data .= str_repeat(chr($pad), $pad);

        $encrypted = openssl_encrypt(
            $data,
            'AES-256-ECB',
            $key,
            OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING
        );

        if ($encrypted === false) {
            throw new \Exception('AES encryption failed.');
        }

        return base64_encode($encrypted);
    }
    
    public function index(){
        $data = User::select(\DB::raw("COUNT(*) as count"), \DB::raw("DAYNAME(created_at) as day_name"), \DB::raw("DAY(created_at) as day"))
        ->where('created_at', '>', Carbon::today()->subDay(6))
        ->groupBy('day_name','day')
        ->orderBy('day')
        ->get();
     $array[] = ['Name', 'Number'];
     foreach($data as $key => $value)
     {
       $array[++$key] = [$value->day_name, $value->count];
     }
    //  return $data;
     return view('backend.index')->with('users', json_encode($array));
    }

    public function profile(){
        $profile=Auth()->user();
        // return $profile;
        return view('backend.users.profile')->with('profile',$profile);
    }

    public function profileUpdate(Request $request,$id){
        // return $request->all();
        $user=User::findOrFail($id);
        $data=$request->all();
        $status=$user->fill($data)->save();
        if($status){
            request()->session()->flash('success','Successfully updated your profile');
        }
        else{
            request()->session()->flash('error','Please try again!');
        }
        return redirect()->back();
    }

    public function settings(){
        $data=Settings::first();
        return view('backend.setting')->with('data',$data);
    }

    public function settingsUpdate(Request $request){
        // return $request->all();
        $this->validate($request,[
            'short_des'=>'required|string',
            'description'=>'required|string',
            'address'=>'required|string',
            'email'=>'required|email',
            'phone'=>'required|string',
        ]);
        $data=$request->all();
        if($request->hasFile('logo') && $request->file('logo')->isValid()){
            $imageName = mt_rand(10000000000,99999999999).'.'.$request->logo->extension();  
            $request->logo->move(public_path('storage/photos/1'), $imageName);
            $data['logo'] = '/storage/photos/1/'.$imageName;
        }
        if($request->hasFile('photo') && $request->file('photo')->isValid()){
            $imageName = mt_rand(10000000000,99999999999).'.'.$request->photo->extension();  
            $request->photo->move(public_path('storage/photos/1'), $imageName);
            $data['photo'] = '/storage/photos/1/'.$imageName;
        }
        // dd($data);
        $settings=Settings::first();
        // return $settings;
        $status=$settings->fill($data)->save();
        if($status){
            request()->session()->flash('success','Setting successfully updated');
        }
        else{
            request()->session()->flash('error','Please try again');
        }
        return redirect()->route('admin');
    }

    public function changePassword(){
        return view('backend.layouts.changePassword');
    }
    public function changPasswordStore(Request $request)
    {
        $request->validate([
            'current_password' => ['required', new MatchOldPassword],
            'new_password' => ['required'],
            'new_confirm_password' => ['same:new_password'],
        ]);

        User::find(auth()->user()->id)->update(['password'=> Hash::make($request->new_password)]);

        return redirect()->route('admin')->with('success','Password successfully changed');
    }

    // Pie chart
    public function userPieChart(Request $request){
        // dd($request->all());
        $data = User::select(\DB::raw("COUNT(*) as count"), \DB::raw("DAYNAME(created_at) as day_name"), \DB::raw("DAY(created_at) as day"))
        ->where('created_at', '>', Carbon::today()->subDay(6))
        ->groupBy('day_name','day')
        ->orderBy('day')
        ->get();
     $array[] = ['Name', 'Number'];
     foreach($data as $key => $value)
     {
       $array[++$key] = [$value->day_name, $value->count];
     }
    //  return $data;
     return view('backend.index')->with('course', json_encode($array));
    }

    // public function activity(){
    //     return Activity::all();
    //     $activity= Activity::all();
    //     return view('backend.layouts.activity')->with('activities',$activity);
    // }

    public function storageLink(){
        // check if the storage folder already linked;
        if(File::exists(public_path('storage'))){
            // removed the existing symbolic link
            File::delete(public_path('storage'));

            //Regenerate the storage link folder
            try{
                Artisan::call('storage:link');
                request()->session()->flash('success', 'Successfully storage linked.');
                return redirect()->back();
            }
            catch(\Exception $exception){
                request()->session()->flash('error', $exception->getMessage());
                return redirect()->back();
            }
        }
        else{
            try{
                Artisan::call('storage:link');
                request()->session()->flash('success', 'Successfully storage linked.');
                return redirect()->back();
            }
            catch(\Exception $exception){
                request()->session()->flash('error', $exception->getMessage());
                return redirect()->back();
            }
        }
    }
}
