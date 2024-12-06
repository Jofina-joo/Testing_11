/*
This kotlin file is used to send Whatsapp & SMS messages based on push notification

Version : 1.0
Author : Sabena yasmin (YJ0008)
Date : 28-Mar-2024
*/

package com.example.background_app;

import android.annotation.SuppressLint
import android.app.DownloadManager
import android.content.*
import android.net.Uri
import android.os.*
import android.os.StrictMode.VmPolicy
import android.util.Log
import androidx.core.content.FileProvider
import com.google.firebase.messaging.FirebaseMessagingService
import com.google.firebase.messaging.RemoteMessage
import kotlinx.coroutines.*
import okhttp3.Call
import okhttp3.Callback
import okhttp3.OkHttpClient
import okhttp3.Response
import org.json.JSONObject
import java.io.File
import java.io.IOException
import java.net.HttpURLConnection
import java.net.URL
import java.net.URLEncoder
import java.time.Instant
import java.time.ZoneOffset
import java.time.format.DateTimeFormatter

//call apiUrl function
val apiUrl = ApiUrl()

val WHATSAPP_PACKAGE_NAME = "com.whatsapp.w4b"

class MyFirebaseMessagingService : FirebaseMessagingService()
{

    @SuppressLint("CommitPrefEdits")
    //Initialize values
     var wtspjob: Job? = null
     var smsjob: Job? = null
     var reportjob: Job? = null
     var is_stop = false
     var is_sms_stop = false
     var is_update = false
    private var onComplete: BroadcastReceiver? = null

    //perform action with update Newtoken
    override fun onNewToken(token: String)
    {
        println("***Coming received 1 ***")

        super.onNewToken(token)
        SharedprefHelper.setFCMToken(this, token)
        Log.e("newToken", token)
    }
    @SuppressLint("Recycle")

    //call while message received
    override fun onMessageReceived(remoteMessage: RemoteMessage)
    {

        println("***message received***")
        super.onMessageReceived(remoteMessage)
        println("From: ${remoteMessage.from}")

        // Check if message contains a data payload.
        if (remoteMessage.data.isNotEmpty()) {
            println("Message data payload ************************************************: ${remoteMessage.data}")

        }
       //Wake Lock after received notification while mobile screen is in off mode
        startActivity(Intent(this, lockscreen::class.java).apply {
            flags = Intent.FLAG_ACTIVITY_NEW_TASK
        })
        val title = remoteMessage.data.getValue("title"); //get title from push notification
        val body = remoteMessage.data.getValue("bodyText"); //get body from push notification
        val selected_userID = remoteMessage.data.getValue("selected_user_id"); //get selected_user_id from push notification
        println("body"+body)

        // Get SMS Report
        if (body == "SMS_Report")
        {
            sms_report("${apiUrl.LiveUrl + apiUrl.get_report_sms}", title, selected_userID)
        }
        //Send SMS Message
        else if (body == "SMS_MSG")
        {
            val sms_product_id = remoteMessage.data.getValue("product_id");
            sent_sms("${apiUrl.LiveUrl + apiUrl.get_task_sms}", title, selected_userID,sms_product_id)
        }
        //Get Whatsapp Report
        else if (body == "WTSP_Report")
        {
            wp_report("${apiUrl.LiveUrl + apiUrl.get_report}", title, selected_userID)
        }
        //Send Whatsapp Message
        else if (body == "WTSP_MSG")
        {
            println("body"+body)
            val product_id = remoteMessage.data.getValue("product_id");  //get product_id from push notification
            sent_wp("${apiUrl.LiveUrl + apiUrl.get_task}", title, selected_userID,product_id)
        }
        //Send RCS Message
        else if (body == "RCS_MSG")
        {
            val rcs_product_id = remoteMessage.data.getValue("product_id");
            sent_rcs("${apiUrl.LiveUrl + apiUrl.get_task_rcs}", title, selected_userID, rcs_product_id)
        }
        //Block Whatsapp Number
        else if (body == "WTSP_BLOCK")
        {
            block_wtsp("${apiUrl.LiveUrl + apiUrl.get_task_block}", title, selected_userID)
        }
        //Stop Whatsapp Campaign
        else if (body == "WTSP_STOP_CAMPAIGN")
        {
            //To cancel coroutine
            wtspjob?.cancel()
            is_stop = true
            //To receive notification after stopped
            val intent = Intent("SHOW_STOP_CAMPAIGN_DIALOG")
            applicationContext.sendBroadcast(intent)
        }
        else if (body == "SMS_STOP_CAMPAIGN")
        {
            //To cancel coroutine
            smsjob?.cancel()
            is_sms_stop = true
            //To receive notification after stopped
            val intent = Intent("SHOW_STOP_CAMPAIGN_DIALOG")
            applicationContext.sendBroadcast(intent)
        }
        //Update App's Latest Version
        else if (body == "APP_VERSION")
        {
            val sender_numbers = remoteMessage.data.getValue("sender_numbers_active");
            val app_update_id = remoteMessage.data.getValue("app_update_id"); //get app_update_id from push notification
            // Split the URL by the last '/'
            val parts = title.split('/')
            // Get the last part of the URL, which is the filename
            val filename = parts.last()
            // Splitting the file name using '_' as the delimiter
            val version_parts1 = filename.split("_")
            // Extracting the desired substring
            val latest_version = version_parts1[1]
            val Version_senderID = SharedprefHelper.getVersion(this)
            val version_parts2 = Version_senderID.toString().split(" ")
            // Extracting the desired version number
            val current_version = version_parts2[1]
            //Check if current app version is not a latest version
            if(current_version!=latest_version)
            {
                // Create a DownloadManager request
                val request = DownloadManager.Request(Uri.parse(title))
                .setTitle("MessageApp Update")
                .setDescription("Downloading update...")
                .setNotificationVisibility(DownloadManager.Request.VISIBILITY_VISIBLE_NOTIFY_COMPLETED)
                .setDestinationInExternalPublicDir(Environment.DIRECTORY_DOWNLOADS, "$filename")
                // Get the DownloadManager service and enqueue the request
                val downloadManager = getSystemService(Context.DOWNLOAD_SERVICE) as DownloadManager
                val downloadId = downloadManager.enqueue(request)
                // Set up a BroadcastReceiver to listen for the download completion
                val onComplete = object : BroadcastReceiver() {
                override fun onReceive(context: Context?, intent: Intent?)
                {
                    val id = intent?.getLongExtra(DownloadManager.EXTRA_DOWNLOAD_ID, -1)
                    if (id == downloadId)
                    {
                        // Unregister the receiver to avoid memory leaks
                        // unregisterReceiver(this)
                        // Start the installation intent
                        val installIntent = Intent(Intent.ACTION_VIEW)
                        val downloadFileUri = downloadManager.getUriForDownloadedFile(downloadId)
                        installIntent.setDataAndType(
                            downloadFileUri,
                            "application/vnd.android.package-archive"
                        )
                        //installIntent.flags = Intent.FLAG_ACTIVITY_NEW_TASK or Intent.FLAG_GRANT_READ_URI_PERMISSION
                        installIntent.flags =
                            Intent.FLAG_ACTIVITY_NEW_TASK or Intent.FLAG_ACTIVITY_CLEAR_TOP or Intent.FLAG_GRANT_READ_URI_PERMISSION
                        startActivity(installIntent)
                    }
                }
            }

            // Register the BroadcastReceiver to listen for download completion
                applicationContext.registerReceiver(onComplete, IntentFilter(DownloadManager.ACTION_DOWNLOAD_COMPLETE))
            //Store app details value in sharedpreference for reference
            SharedprefHelper.setAppupdated(this, true)
            SharedprefHelper.setTitle(this, title)
            SharedprefHelper.setAppUpdateID(this, app_update_id)
            SharedprefHelper.setSenderNumbers(this, sender_numbers)
        }
            //Otherwise,update version to verify app version already exist
            else
            {
                update_version("${apiUrl.LiveUrl+ apiUrl.update_task_version}",title,app_update_id,"1",sender_numbers)
            }
        }
        //Get RCS Report
        else{
            rcs_report("${apiUrl.LiveUrl + apiUrl.get_report_rcs}", title, selected_userID)
        }
    }


    //Start Function - Block Whatsapp
    fun block_wtsp(api_url: String,compose_id:String,select_user_id:String)
    {
       var back_wtspblock = false
       //To create http request
       val client = OkHttpClient()
       val mobileNumber= SharedprefHelper.getMobileNumber(this)
       SharedprefHelper.set_Block(this,true)
       SharedprefHelper.setReceiverNo(this@MyFirebaseMessagingService,"")
       SharedprefHelper.setBlockedRep(this@MyFirebaseMessagingService,"")
       //put request as json format
       val JSONObjectString = JSONObject()
       JSONObjectString.put("mobile_number", mobileNumber)
       JSONObjectString.put("com_msg_block_id", compose_id)
       JSONObjectString.put("selected_user_id", select_user_id)
       GlobalScope.launch(Dispatchers.Main) {//coroutine that runs asynchronously on the main (UI) thread
           val requestBuilder = CommonAPI.httpPost("post", api_url, JSONObjectString)
           val request = requestBuilder?.build()
           client.newCall(request).enqueue(object : Callback {     //send asynchronous http request with callback function
            //Failure response
               override fun onFailure(call: Call, e: IOException) {
                   //Handle failure response
                   println(e)
               }
            //Success response
               override fun onResponse(call: Call, response: Response){ //Handle success response
                   val r = response.body()?.string()
                   val json = r?.let { JSONObject(it) };
                   if(response.code() == 200 && json?.getString("response_code") == "1"){ //check if response code is '200' continue process
                       val number_array = json.getJSONArray("numbers")
                       var i=0;
                       try {
                           while (i < number_array.length()){ //loop for receiver numbers
                               var toNumber :String = number_array[i] as String;
                               toNumber = toNumber.replace("+", "").replace(" ", "")
                               SharedprefHelper.setReceiverNo(this@MyFirebaseMessagingService,toNumber)
                             //Navigate to receiver number whatsapp chat page
                               val uri = Uri.parse("smsto:$toNumber")
                               var intent = Intent(Intent.ACTION_SENDTO, uri)
                               intent.setPackage(WHATSAPP_PACKAGE_NAME)
                               intent.addFlags(Intent.FLAG_ACTIVITY_NEW_TASK or Intent.FLAG_ACTIVITY_MULTIPLE_TASK);
                               startActivity(intent)
                               //Give delay to complete block whatsapp
                               Thread.sleep(10000)
                               i++;
                           }
                           SharedprefHelper.set_Block(this@MyFirebaseMessagingService,false)
                           val reports= SharedprefHelper.getWPBlockReport(this@MyFirebaseMessagingService)
                           if (reports != null) { //check if report not equal to null, update sent data
                               update_block_sts("${apiUrl.LiveUrl+ apiUrl.update_task_block}",compose_id,reports,select_user_id)
                           }
                           //Back Navigation
                           back_wtspblock = true
                           if(back_wtspblock == true) {
                               backNavigate(true)
                           }
                       }
                       catch (ignored: Exception){
                           println(ignored)
                       }
                   }
                   else{
                       if (json != null) {
                           println(json.getString("response_msg"))
                       }
                   }
               }
           })
       }
   } //End Function - Block Whatsapp

    //Start Function - Send SMS
    fun sent_sms(api_url: String,compose_id:String,select_user_id:String,sms_product_id:String)
    {
        SharedprefHelper.setsentsmsflag(this@MyFirebaseMessagingService,true)
        var back_sms = false
        //To prevent Issue while Image attachment on google message app
        val builder = VmPolicy.Builder()
        StrictMode.setVmPolicy(builder.build())
        //To create http request
        val client = OkHttpClient()
        val mobileNumber= SharedprefHelper.getMobileNumber(this)
        //get request ID
        var REQ_ID =  req_ID(this);
        //put request as json format
        val JSONObjectString = JSONObject()
        JSONObjectString.put("mobile_number", mobileNumber)
        JSONObjectString.put("compose_message_id", compose_id)
        JSONObjectString.put("selected_user_id", select_user_id)
        JSONObjectString.put("request_id", REQ_ID)
        GlobalScope.launch(Dispatchers.Main) {   //coroutine that runs asynchronously on the main (UI) thread
            val requestBuilder = CommonAPI.httpPost("post", api_url, JSONObjectString) //send request commonAPI function and process the response
            val request = requestBuilder?.build()
            //send asynchronous http request with callback function
            client.newCall(request).enqueue(object : Callback {
                //Failure response
                override fun onFailure(call: Call, e: IOException) {
                    //Handle failure response
                    println(e)
                }
                //Success response
                @SuppressLint("SuspiciousIndentation")
                override fun onResponse(call: Call, response: Response){  //Handle success response
                    val r = response.body()?.string()  //response body
                    val json = r?.let { JSONObject(it) };
                    if(response.code() == 200 && json?.getString("response_code") == "1"){ //check if response code is 200, then continue process
                        val number_array = json.getJSONArray("numbers")
                        val message = json.getJSONArray("messages")
                        var i=0;
                        try {
                            SharedprefHelper.setSMSSentRep(this@MyFirebaseMessagingService,"")
                            SharedprefHelper.setSMSRecNo(this@MyFirebaseMessagingService,"")
                            //kotlin coroutine exception handling
                            val exceptionHandler = CoroutineExceptionHandler { _, exception -> // Handle the exception (e.g., log it)
                                exception.printStackTrace()
                            }
                            //To implement kotlin coroutine to stop whatsapp campaign
                            val coroutineScope = CoroutineScope(Job() + Dispatchers.Default)
                            // Store coroutine to job variable for stop sms process
                            smsjob = coroutineScope.launch(exceptionHandler) {
                                //Loop through receiver numbers
                                while (i < number_array.length() && isActive)
                                {
                                    //Store receiver number data before start process, If process failed then update as failed for receiver number
                                    val sms_sent = SharedprefHelper.getSMSSentReport(this@MyFirebaseMessagingService)
                                    var sent_time_Data = ""
                                    if (sms_sent == "") {
                                        sent_time_Data = "${number_array[i]}||"
                                    } else {
                                        sent_time_Data = "$sms_sent˜${number_array[i]}||"
                                    }
                                    SharedprefHelper.setSMSSentRep(this@MyFirebaseMessagingService, sent_time_Data)

                                    // loop for receiver number
                                    var toNumber :String = number_array[i] as String;
                                    toNumber = toNumber.replace("+", "").replace(" ", "")
                                    SharedprefHelper.setSMSRecNo(this@MyFirebaseMessagingService,toNumber)
                                    val messageToSend = message[i]
                                    //Navigate to sms message
                                    val uri = Uri.parse("smsto:+$toNumber")
                                    var intent = Intent(Intent.ACTION_SENDTO, uri)
                                    intent.putExtra("sms_body", "$messageToSend")
                                    //SMS App package
                                    intent.setPackage("com.android.mms")
                                    intent.addFlags(Intent.FLAG_ACTIVITY_NEW_TASK or Intent.FLAG_ACTIVITY_MULTIPLE_TASK);
                                    startActivity(intent)
                                    Thread.sleep(10000) // give delay to navigate
                                    i++;
                                }
                                //Store sms report
                                SharedprefHelper.setsentsmsflag(this@MyFirebaseMessagingService,false)
                                val sent_report=SharedprefHelper.getSMSSentReport(this@MyFirebaseMessagingService)
                                println("sent_report"+sent_report)
                                //Update report
                                if (sent_report != null) { //check if report not equal to null, update sent data
                                    if(is_sms_stop == true)
                                    {
                                        //    update_sentSMS("${apiUrl.LiveUrl+ apiUrl.update_task_stopsms}",compose_id,sent_report,select_user_id)
                                    }
                                    else
                                    {
                                        update_sentSMS("${apiUrl.LiveUrl+ apiUrl.update_task_sms}",compose_id,sent_report,select_user_id,sms_product_id)
                                    }
                                }
                                // Back Navigation
                                back_sms = true
                                if(back_sms == true) {
                                    backNavigate(true)
                                }
                            }
                        }
                        catch (ignored: Exception){
                            println("err:$ignored")
                        }
                    }
                    else{
                        if (json != null) {
                            println(json.getString("response_msg"))
                        }
                    }
                }
            })
        }
    }


  //  Start Function - Send RCS
    fun sent_rcs(api_url: String, compose_id: String,select_user_id:String, rcs_product_id:String) {
      SharedprefHelper.setsentrcsflag(this@MyFirebaseMessagingService,true)
        var back_rcs = false
        //To prevent issue while Image attachment on google message app
        val builder = VmPolicy.Builder()
        StrictMode.setVmPolicy(builder.build())
        //to create http request
        val client = OkHttpClient()
        val mobileNumber= SharedprefHelper.getMobileNumber(this)
        //get request ID
        var REQ_ID =  req_ID(this);
        //put request as json format
        val JSONObjectString = JSONObject()
        JSONObjectString.put("mobile_number", mobileNumber)
        JSONObjectString.put("compose_message_id", compose_id)
        JSONObjectString.put("selected_user_id", select_user_id)
        JSONObjectString.put("request_id", REQ_ID)
        GlobalScope.launch(Dispatchers.Main) {//coroutine that runs asynchronously on the main (UI) thread
            val requestBuilder = CommonAPI.httpPost("post", api_url, JSONObjectString)
            val request = requestBuilder?.build()
            client.newCall(request).enqueue(object : Callback {     //send asynchronous http request with callback function
              //Failure Response
                override fun onFailure(call: Call, e: IOException) {
                    //Handle failure response
                    println(e)
                }
                //Success Response
                override fun onResponse(call: Call, response: Response){ //Handle success response
                    val r = response.body()?.string()
                    val json = r?.let { JSONObject(it) };
                    println("RCS message"+json)
                    if(response.code() == 200 && json?.getString("response_code") == "1"){ //check if response code is 200, then continue process
                        val number_array = json.getJSONArray("numbers")
                        val message = json.getJSONArray("messages")
                        val media = json.getJSONArray("media_url")
                        println("media"+media)
                        var media_flag = "-";
                        var file_path_url = "";
                        if(media.length() == 0){
                            media_flag = "-"
                        }
                        else if(media.length() == 1){
                            media_flag = "true";
                        }
                        else{
                            media_flag = "false";
                        }
                       //check if media url is exist
                        if(media_flag == "true"){
                            val scope = CoroutineScope(Job() + Dispatchers.Main)
                            scope.launch {
                                val strs = media[0].toString().split("/").toTypedArray()
                                file_path_url = Environment.getExternalStoragePublicDirectory(Environment.DIRECTORY_DOWNLOADS)
                                    .toString() + "/"+strs[strs.lastIndex]
                                val file = File(file_path_url)
                                downloadFile(strs[strs.lastIndex],"whatsapp_apk",media[0].toString())
                            }
                            Thread.sleep(10000) //give time to download
                        }
                        // check if media url is already exist
                        if(media_flag == "true"){
                            val check = downloadCheck(file_path_url) // check the respective media file already exist
                            if (check){
                                println("file exists")
                            }
                            else{ //Otherwise check continously with 10 sec until the media file found
                                Thread.sleep(10000)
                                val check_2 = false;
                                while(!check_2){
                                    val check_2 = downloadCheck(file_path_url)
                                    if(check_2){
                                        break;
                                    }
                                    else{
                                        Thread.sleep(10000)
                                    }
                                }
                            }
                        }
                        var i=0;
                        try {
                            SharedprefHelper.setRCSSentRep(this@MyFirebaseMessagingService,"")
                            SharedprefHelper.setRCSRecNo(this@MyFirebaseMessagingService,"")
                            while (i < number_array.length()){ //loop for receiver numbers
                                var file_name = ""
                                //check if media url is exist
                                if(media_flag == "false"){
                                    val scope = CoroutineScope(Job() + Dispatchers.Main)
                                    var get_file_type_temp = media[i].toString().split("/").toTypedArray()
                                    var get_file_type = get_file_type_temp[get_file_type_temp.lastIndex].toString().split(".").toTypedArray()
                                    file_name = number_array[i].toString()+"."+get_file_type[get_file_type.lastIndex]
                                    scope.launch {
                                        downloadFile(file_name,"whatsapp_apk",media[i].toString())
                                    }
                                    Thread.sleep(10000) //give time to download
                                }
                                if(media_flag == "false"){
                                    file_path_url = Environment.getExternalStoragePublicDirectory(Environment.DIRECTORY_DOWNLOADS)
                                        .toString() + "/"+file_name
                                    val check = downloadCheck(file_path_url) // check the respective media file already exist
                                    if (check){
                                        println("file exists")
                                    }
                                    else{ //Otherwise check continously with 10 sec until the media file found
                                        Thread.sleep(10000)
                                        val check_2 = false;
                                        while(!check_2){
                                            val check_2 = downloadCheck(file_path_url)
                                            if(check_2){
                                                break;
                                            }
                                            else{
                                                Thread.sleep(10000)
                                            }
                                        }
                                    }
                                }

                                // Store receiver number data before starting process
                                val rcs_sent = SharedprefHelper.getRCSSentReport(this@MyFirebaseMessagingService)
                                var sent_time_Data = ""
                                if (rcs_sent == "") {
                                    sent_time_Data = "${number_array[i]}||"
                                } else {
                                    sent_time_Data = "$rcs_sent˜${number_array[i]}||"
                                }
                                SharedprefHelper.setRCSSentRep(this@MyFirebaseMessagingService, sent_time_Data)


                                var toNumber :String = number_array[i] as String;
                                toNumber = toNumber.replace("+", "").replace(" ", "")
                                println("toNumber"+toNumber)
                                SharedprefHelper.setRCSRecNo(this@MyFirebaseMessagingService,toNumber)
                                val messageToSend = message[i]

                                println("toNumber"+toNumber)
                                println("messageToSend"+messageToSend)
                                // Navigate to respective chat on RCS message
                                val intent = Intent()
                                intent.action = Intent.ACTION_SEND
                                intent.putExtra("address", "$toNumber")  //receiver number
                                intent.putExtra(Intent.EXTRA_TEXT,"$messageToSend") // message
                                intent.type = "text/plain" //message format

                                Log.d("Intent Configuration", "Action: ${intent.action}")
                                Log.d("Intent Configuration", "Type: ${intent.type}")
                                //Send Message with media
                                if(media_flag != "-") {
                                    println("1")
                                    val file = File(file_path_url)
                                    if (file.exists()) {
                                        println("2")
                                         val imageUri = Uri.fromFile(file) // Create a URI from the file
                                        println("imageUri"+imageUri)
                                     //   val imageUri = FileProvider.getUriForFile(this@MyFirebaseMessagingService, "${applicationContext.packageName}.fileprovider", file)
                                        intent.putExtra(Intent.EXTRA_STREAM, imageUri)
                                        intent.addFlags(Intent.FLAG_GRANT_READ_URI_PERMISSION)
                                      //  intent.type = json.getString("message_type")+"/*"
                                        intent.type = "image/*"  // Use lowercase
                                        Log.d("Intent Configuration", "Action: ${intent.action}")
                                        Log.d("Intent Configuration", "Type: ${intent.type}")

                                       // intent.type = "IMAGE/*"
                                    } else {
                                        println("File not exist...")
                                    }
//
//                                    if (file.exists()) {
//                                        val imageUri = FileProvider.getUriForFile(
//                                            this@MyFirebaseMessagingService,
//                                            "${applicationContext.packageName}.fileprovider",
//                                            file
//                                        )
//                                        intent.putExtra(Intent.EXTRA_STREAM, imageUri)
//                                        intent.addFlags(Intent.FLAG_GRANT_READ_URI_PERMISSION)
//                                        intent.type = "image/*"  // Use lowercase
//                                        Log.d("Intent Configuration", "Action: ${intent.action}")
//                                        Log.d("Intent Configuration", "Type: ${intent.type}")
//                                    } else {
//                                        println("File not exist...")
//                                    }

                                }
                                println("3")
                                intent.setPackage("com.google.android.apps.messaging")
                                println("4")
                                intent.addFlags(Intent.FLAG_ACTIVITY_NEW_TASK or Intent.FLAG_ACTIVITY_MULTIPLE_TASK);
                                println("5")
                                startActivity(intent)
                                println("6")
                                Thread.sleep(4000) // give time to navigate
                                //Delete File
                                if(media_flag == "false") {
                                    val fdelete: File = File(file_path_url)
                                    if (fdelete.exists()) {
                                        if (fdelete.delete()) {
                                            println("file Deleted :" + file_path_url)
                                        } else {
                                            println("file not Deleted :" + file_path_url)
                                        }
                                    }
                                }

//                                val rcs_sent = SharedprefHelper.getRCSSentReport(this@MyFirebaseMessagingService)
//                                println("rcs_sent"+rcs_sent)
//                                var sent_time_Data = ""
//                                if (rcs_sent == "") {
//                                    sent_time_Data = "${number_array[i]}||"
//                                }
//                                else {
//                                    sent_time_Data = "${number_array[i]}||"
//                                }
//                                SharedprefHelper.setRCSSentRep(this@MyFirebaseMessagingService, sent_time_Data)

                                i++;
                            }
                            if(media_flag == "true") {
                                val fdelete: File = File(file_path_url)
                                if (fdelete.exists()) {
                                    if (fdelete.delete()) {
                                        println("file Deleted :" + file_path_url)
                                    } else {
                                        println("file not Deleted :" + file_path_url)
                                    }
                                }
                            }
                            SharedprefHelper.setsentrcsflag(this@MyFirebaseMessagingService,false)
                            val sent_report=SharedprefHelper.getRCSSentReport(this@MyFirebaseMessagingService)
                            //val sent_report=SharedprefHelper.getSMSSentReport(this@MyFirebaseMessagingService)
                            println("sent_report"+sent_report)
                            if (sent_report != null) { //check if report not equal to null, update sent data
                                update_sentRCS(
                                    "${apiUrl.LiveUrl + apiUrl.update_task_rcs}",
                                    compose_id,
                                    sent_report,
                                    select_user_id,
                                    rcs_product_id
                                )
                            }
                            //Back Navigation
                            back_rcs = true
                            if(back_rcs == true) {
                                backNavigate(true)
                            }
                        }
                        catch (ignored: Exception){
                            println("err:$ignored")
                        }
                    }
                    else{
                        if (json != null) {
                            println(json.getString("response_msg"))
                        }
                    }
                }
            })
        }
    }
 //End Function to send RCS Message


//Start Function - Whatsapp Report
    fun wp_report(api_url: String,compose_id:String,select_user_id:String) {
        SharedprefHelper.set_checkblock(this@MyFirebaseMessagingService, false)
        var backrep_wtsp = false
        //To create http request
        val client = OkHttpClient()
        val mobileNumber= SharedprefHelper.getMobileNumber(this)
        SharedprefHelper.setWPReport(this,true)
        SharedprefHelper.setWPDelRep(this,"")
        val strs = compose_id.split("-").toTypedArray()
        //get request ID
        var REQ_ID =  req_ID(this);
        //put request as json format
        SharedprefHelper.set_rep_str(this@MyFirebaseMessagingService,strs[0])
        SharedprefHelper.set_rep_selectID(this@MyFirebaseMessagingService,select_user_id)
        val selectID = SharedprefHelper.get_rep_selectID(this)
        val JSONObjectString = JSONObject()
        JSONObjectString.put("mobile_number", mobileNumber)
        JSONObjectString.put("compose_whatsapp_id", strs[0])
        JSONObjectString.put("selected_user_id", select_user_id)
        JSONObjectString.put("request_id", REQ_ID)
        //If receiver numbers exist
            if(strs.size != 1 ){
                JSONObjectString.put("receiver_number", strs[1])
            }
        GlobalScope.launch(Dispatchers.Main) { //coroutine that runs asynchronously on the main (UI) thread
            val requestBuilder = CommonAPI.httpPost("post", api_url, JSONObjectString)
            val request = requestBuilder?.build()
            //send asynchronous http request with callback function
            client.newCall(request).enqueue(object : Callback {
                //Failure Response
                override fun onFailure(call: Call, e: IOException) {
                    println(e)
                    //Handle failure response
                }
                //Success Response
                override fun onResponse(call: Call, response: Response){ //  //Handle success response
                    val r = response.body()?.string()
                    val json = r?.let { JSONObject(it) };
                    if(response.code() == 200 && json?.getString("response_code") == "1"){ //check if response code is 200, then continue process
                        val number_array = json.getJSONArray("numbers")
                        val messages = json.getJSONArray("messages")
                        var i=0;
                        try {
                            //kotlin coroutine exception handling
                            val exceptionHandler = CoroutineExceptionHandler { _, exception ->
                                // Handle the exception (e.g., log it)
                                exception.printStackTrace()
                            }
                            val coroutineScope = CoroutineScope(Job() + Dispatchers.Default)
                            // Store coroutine to job variable for stop process
                            reportjob = coroutineScope.launch(exceptionHandler) {
                            while (i < number_array.length()){ //loop for receiver numbers
                                val block_sts= SharedprefHelper.get_checkblock(this@MyFirebaseMessagingService)
                                if(!block_sts)
                                {
                                var toNumber :String = number_array[i] as String;
                                toNumber = toNumber.replace("+", "").replace(" ", "")
                                    var msg_search = "";
                                    if(messages[i].toString().length <700){ // check if message have less than 700 characters
                                        //set search message value
                                        msg_search = messages[i].toString().trim()
                                    }
                                    else{
                                        msg_search = messages[i].toString().substring(0,700).trim()
                                    }
                                     msg_search = msg_search.replace("*","")
                                    SharedprefHelper.setSearchMSG(this@MyFirebaseMessagingService,msg_search)
                                //Scroll to previous message
                                SharedprefHelper.setScroll(this@MyFirebaseMessagingService,true)
                                SharedprefHelper.setScrollInitial(this@MyFirebaseMessagingService,true)
                                //set search number value
                                SharedprefHelper.setWPSearchNo(this@MyFirebaseMessagingService,toNumber)
                                //Use URI method to navigate respective receiver number chat page
                                val uri = Uri.parse("smsto:$toNumber")
                                var intent = Intent(Intent.ACTION_SENDTO, uri)
                                intent.setPackage(WHATSAPP_PACKAGE_NAME)
                                intent.addFlags(Intent.FLAG_ACTIVITY_NEW_TASK or Intent.FLAG_ACTIVITY_MULTIPLE_TASK);
                                startActivity(intent)
                                Thread.sleep(15000) //give time to navigate
                                }
                                i++;
                            }
                                SharedprefHelper.setFlagValue(this@MyFirebaseMessagingService, false)

                                SharedprefHelper.setWPReport(this@MyFirebaseMessagingService,false)
                            SharedprefHelper.setSearchMSG(this@MyFirebaseMessagingService,"")
                            val reports=  SharedprefHelper.getWPDelReport(this@MyFirebaseMessagingService)
                               // val del_report_null= SharedprefHelper.get_delreport_null(this@MyFirebaseMessagingService)
                                val del_report_null = SharedprefHelper.get_checkblock(this@MyFirebaseMessagingService)
                            if (!del_report_null && reports != null) {  //check if report data is available , update report data
                                update_deliveryWP("${apiUrl.LiveUrl+ apiUrl.update_report}",strs[0],reports,select_user_id)
                            }
                        }
                        }
                        catch (ignored: Exception){
                            println(ignored)
                        }
                    }
                    else{
                        if (json != null) {
                            println(json.getString("response_msg"))
                        }
                    }
                }
            })
        }
    }
    //End Function - Whatsapp Report

    //Start Function - SMS Report
    fun sms_report(api_url: String, compose_id: String, select_user_id: String) {
        println("1")
        var backrep_sms = false
        //To create http request
        val client = OkHttpClient()
        val mobileNumber= SharedprefHelper.getMobileNumber(this)
         SharedprefHelper.setSMSReport(this,true)
        SharedprefHelper.setSMSDelRep(this,"")
        val strs = compose_id.split("-").toTypedArray()
        //add request ID
        var REQ_ID =  req_ID(this);
        //put request as json format
        val JSONObjectString = JSONObject()
        JSONObjectString.put("mobile_number", mobileNumber)
        JSONObjectString.put("compose_message_id", strs[0])
        JSONObjectString.put("selected_user_id", select_user_id)
        JSONObjectString.put("request_id", REQ_ID)
        GlobalScope.launch(Dispatchers.Main) {//coroutine that runs asynchronously on the main (UI) thread
       //if receiver number exist
        if(strs.size != 1 ){
            println("2")
            JSONObjectString.put("receiver_number", strs[1])
        }
            val requestBuilder = CommonAPI.httpPost("post", api_url, JSONObjectString)
            val request = requestBuilder?.build()
            //send asynchronous http request with callback function
            client.newCall(request).enqueue(object : Callback {
            //Failure response
            override fun onFailure(call: Call, e: IOException) {
                //Handle failure response
                println(e)
            }
                //Success reponse
            override fun onResponse(call: Call, response: Response){ //Handle success response
                val r = response.body()?.string()
                val json = r?.let { JSONObject(it) };
                if(response.code() == 200 && json?.getString("response_code") == "1"){ //check if response code is 200, then continue process
                    val number_array = json.getJSONArray("numbers")
                    val messages = json.getJSONArray("messages")
                    var i=0;
                    try {
                        while (i < number_array.length()){  // Loop for receiver numbers
                            println("3")
                            var toNumber :String = number_array[i] as String;
                            toNumber = toNumber.replace("+", "").replace(" ", "")
                            if(messages[i].toString().length <55){ //check if message characters less than 55
                                println("4")
                                SharedprefHelper.setSearchMSG(this@MyFirebaseMessagingService,messages[i].toString().trim())
                            }
                            else{ //Otherwise take first 50 characters
                                println("4")
                                SharedprefHelper.setSearchMSG(this@MyFirebaseMessagingService,messages[i].toString().substring(0,50).trim())
                            }
                            SharedprefHelper.setSMSSearchNo(this@MyFirebaseMessagingService,toNumber)
                            println("5")
                            //Navigate to respective chat on SMS app
                            val uri = Uri.parse("smsto:+$toNumber")
                            println("6")
                            var intent = Intent(Intent.ACTION_SENDTO, uri)
                            intent.setPackage("com.android.mms")
                            intent.addFlags(Intent.FLAG_ACTIVITY_NEW_TASK or Intent.FLAG_ACTIVITY_MULTIPLE_TASK);
                            println("7")
                            startActivity(intent)
                            println("8")
                            Thread.sleep(10000) //give time to navigate
                            i++;
                        }
                        println("9")
                        SharedprefHelper.setSMSReport(this@MyFirebaseMessagingService,false)
                        SharedprefHelper.setSearchMSG(this@MyFirebaseMessagingService,"")
                        val sms_reports=  SharedprefHelper.getSMSDelReport(this@MyFirebaseMessagingService)
                        if (sms_reports != null) {  //check if report data is available , update report data
                            var sms_reports = sms_reports.replace("\n","||")
                            println("10")
                            update_deliverySMS("${apiUrl.LiveUrl+ apiUrl.update_report_sms}",strs[0],sms_reports,select_user_id)
                        }
                        println("11")
                        //Back navigation
                        backrep_sms = true
                        if(backrep_sms == true) {
                            backNavigate(true)
                        }
                    }
                    catch (ignored: Exception){
                        println(ignored)
                    }
                }
                else{
                    if (json != null) {
                        println(json.getString("response_msg"))
                    }
                }
            }
        })
    }
    }
    //End Function - SMS Report

    //Start Function to RCS Report
    fun rcs_report(api_url: String, compose_id: String, select_user_id: String) {
        var backrep_rcs = false
        //to create http request
        val client = OkHttpClient()
        val mobileNumber= SharedprefHelper.getMobileNumber(this)
        SharedprefHelper.setRCSReport(this,true)
        SharedprefHelper.setRCSDelRep(this,"")
        val strs = compose_id.split("-").toTypedArray()
        //add request ID
        var REQ_ID =  req_ID(this);
        //put request as json format
        val JSONObjectString = JSONObject()
        JSONObjectString.put("mobile_number", mobileNumber)
        JSONObjectString.put("compose_message_id", strs[0])
        JSONObjectString.put("selected_user_id", select_user_id)
        JSONObjectString.put("request_id", REQ_ID)
        GlobalScope.launch(Dispatchers.Main) {//coroutine that runs asynchronously on the main (UI) thread
            if(strs.size != 1 ){
                JSONObjectString.put("receiver_number", strs[1])
            }
            val requestBuilder = CommonAPI.httpPost("post", api_url, JSONObjectString)
            val request = requestBuilder?.build()
            //send asynchronous http request with callback function
            client.newCall(request).enqueue(object : Callback {
             //Failure Reponse
                override fun onFailure(call: Call, e: IOException) {
                    //Handle failure response
                    println(e)
                }
                //Success Response
                override fun onResponse(call: Call, response: Response){ //Handle success response
                    val r = response.body()?.string()
                    val json = r?.let { JSONObject(it) };
                    if(response.code() == 200 && json?.getString("response_code") == "1"){ //check if response code is 200, then continue process
                        val number_array = json.getJSONArray("numbers")
                        val messages = json.getJSONArray("messages")
                        var i=0;
                        try {
                            while (i < number_array.length()){ // loop for receiver numbers
                                var toNumber :String = number_array[i] as String;
                                toNumber = toNumber.replace("+", "").replace(" ", "")// loop for receiver numbers
                                if(messages[i].toString().length <55){ //check if message characters less than 55
                                    SharedprefHelper.setSearchMSGRCS(this@MyFirebaseMessagingService,messages[i].toString().trim())
                                }
                                else{ //Otherwise take first 50 characters
                                    SharedprefHelper.setSearchMSGRCS(this@MyFirebaseMessagingService,messages[i].toString().substring(0,50).trim())
                                }
                                SharedprefHelper.setRCSSearchNo(this@MyFirebaseMessagingService,toNumber)
                                //Navigate to respective receiver number chat on RCS
                                val intent = Intent()
                                intent.action = Intent.ACTION_SEND
                                intent.putExtra("address", "$toNumber")
                                intent.type = "text/plain"
                                intent.setPackage("com.google.android.apps.messaging")
                                intent.addFlags(Intent.FLAG_ACTIVITY_NEW_TASK or Intent.FLAG_ACTIVITY_MULTIPLE_TASK);
                                startActivity(intent)
                                Thread.sleep(10000) //give time to navigate
                                i++;
                            }
                            SharedprefHelper.setRCSReport(this@MyFirebaseMessagingService,false)
                            SharedprefHelper.setSearchMSGRCS(this@MyFirebaseMessagingService,"")
                            val rcs_reports=  SharedprefHelper.getRCSDelReport(this@MyFirebaseMessagingService)
                            if (rcs_reports != null) {  //check if report data is available , update report data
                                var rcs_reports = rcs_reports.replace("\n","||")
                                update_deliveryRCS("${apiUrl.LiveUrl+ apiUrl.update_report_rcs}",strs[0],rcs_reports,select_user_id)
                            }
                            //Back navigation
                            backrep_rcs = true
                            if(  backrep_rcs == true) {
                                backNavigate(true)
                            }
                        }
                        catch (ignored: Exception){
                            println(ignored)
                        }
                    }
                    else{
                        if (json != null) {
                            println(json.getString("response_msg"))
                        }
                    }
                }
            })
        }
    }
    //End Function to RCS Report

    //Start Function - Send Whatsapp Message
    fun sent_wp(api_url: String, compose_id: String,select_user_id:String,product_id:String) {
        println("Coming")
        SharedprefHelper.setFlagValue(this@MyFirebaseMessagingService,false)
        SharedprefHelper.set_checkblock(this@MyFirebaseMessagingService, false)
        SharedprefHelper.set_checkblock(this@MyFirebaseMessagingService, false)
        SharedprefHelper.set_report_null(this@MyFirebaseMessagingService,false)
        SharedprefHelper.set_composeID(this@MyFirebaseMessagingService, compose_id)
        SharedprefHelper.set_selectuserID(this@MyFirebaseMessagingService, select_user_id)
        SharedprefHelper.set_productID(this@MyFirebaseMessagingService, product_id)
        //  var back_wp = false
        //To create http request
        val client = OkHttpClient()
        val mobileNumber= SharedprefHelper.getMobileNumber(this@MyFirebaseMessagingService)
        //get request ID
        var REQ_ID =  req_ID(this@MyFirebaseMessagingService);
        //put request as json format
        val JSONObjectString = JSONObject()
        JSONObjectString.put("mobile_number", mobileNumber)
        JSONObjectString.put("compose_whatsapp_id", compose_id)
        JSONObjectString.put("selected_user_id", select_user_id)
        JSONObjectString.put("request_id", REQ_ID)
        GlobalScope.launch(Dispatchers.Main) {//coroutine that runs asynchronously on the main (UI) thread
            val requestBuilder = CommonAPI.httpPost("post", api_url, JSONObjectString)
            val request = requestBuilder?.build()
            client.newCall(request).enqueue(object : Callback {     //send asynchronous http request with callback function
                //Failure response
                override fun onFailure(call: Call, e: IOException) {
                    //Handle failure response
                    println(e)
                }
                //Success response
                @SuppressLint("SuspiciousIndentation")
                override fun onResponse(call: Call, response: Response){ //Handle success response

                    val r = response.body()?.string()
                    println(r)
                    val json = r?.let { JSONObject(it) };
                    if(response.code() == 200 && json?.getString("response_code") == "1"){ //check if response code is 200, then continue process


                        val number_array = json.getJSONArray("numbers")
                        val message = json.getJSONArray("messages")
                        val media = json.getJSONArray("media_url")
                        val is_samemedia = json.getString("is_samemedia")
                        var media_flag = "-";
                        var file_path_url = "";
                        //If media not exist
                        if(media.length() == 0){
                            media_flag = "-"
                        }
                        //Otherwise check if generic media
                        else if(media.length() == 1 && is_samemedia == "true"){
                            println("After comung function");
                            media_flag = "true";
                        }
                       // Check if personalized media
                        else{
                            media_flag = "false";
                        }
                        // check if media url is already exist
                        if(media_flag == "true"){
                            val scope = CoroutineScope(Job() + Dispatchers.Main)
                            scope.launch {
                                val strs = media[0].toString().split("/").toTypedArray()
                                file_path_url = Environment.getExternalStoragePublicDirectory(Environment.DIRECTORY_DOWNLOADS)
                                    .toString() + "/"+strs[strs.lastIndex]
                                println(file_path_url)
                                val file = File(file_path_url)
                                //val urlRegex = Regex("""^https?://(?:[a-zA-Z0-9-]+\.)+[a-zA-Z]{2,}(?:/[^/]+)*/[^/]+\.(?:jpg|jpeg|png|mp4)$""", RegexOption.IGNORE_CASE)
                                val urlRegex = Regex("""^https?://(?:[a-zA-Z0-9-]+\.[a-zA-Z]{2,}|(?:\d{1,3}\.){3}\d{1,3})(?:/[^/]+)*/[^/]+\.(?:jpg|jpeg|png|mp4)$""", RegexOption.IGNORE_CASE)
                                if(urlRegex.containsMatchIn(media[0].toString())) {
                                    downloadFile(
                                        strs[strs.lastIndex],
                                        "whatsapp_apk",
                                        media[0].toString()
                                    )
                                    println(media[0].toString())
                                }
                                else
                                {
                                    var j=0;
                                    //Receiver numbers length
                                    while (j < number_array.length())
                                        {
                                            var toNumber: String =
                                        number_array[j] as String;
                                    toNumber =
                                        toNumber.replace("+", "").replace(" ", "")
                                    SharedprefHelper.setReceiverNo(
                                        this@MyFirebaseMessagingService,
                                        toNumber
                                    )
                                            //Store receiver number data before start process, If process failed then update as failed for receiver number
                                    val msg_report =
                                        SharedprefHelper.getWPSentReport(this@MyFirebaseMessagingService)
                                    var report_Data = "";
                                    val number =
                                        SharedprefHelper.getReceiverNo(this@MyFirebaseMessagingService)
                                    if (msg_report == "") {
                                        report_Data = number + "||"
                                    } else {
                                        report_Data = msg_report + "˜" + number + "||"
                                    }
                                    SharedprefHelper.setWPSentRep(
                                        this@MyFirebaseMessagingService,
                                        report_Data
                                    )
                                        j++
                                        }
                                }
                            }
                            Thread.sleep(10000) //give time to download
                        }
                        if(media_flag == "true"){
                            val check = downloadCheck(file_path_url) // check the respective media file already exist
                            if (check){
                                println("file exists")
                            }
                            else{ //Otherwise check continously with 10 sec until the media file found
                                Thread.sleep(10000)
                                val check_2 = false;
                                while(!check_2){
                                    val check_2 = downloadCheck(file_path_url)
                                    if(check_2){
                                        break;
                                    }
                                    else{
                                        Thread.sleep(10000)
                                    }
                                }
                            }
                        }
                        var i=0;
                        try {
                            //kotlin coroutine exception handling
                            val exceptionHandler = CoroutineExceptionHandler { _, exception ->
                                // Handle the exception (e.g., log it)
                                exception.printStackTrace()
                            }
                            //To implement kotlin coroutine to stop whatsapp campaign
                            val coroutineScope = CoroutineScope(Job() + Dispatchers.Default)
                            // Store coroutine to job variable
                            wtspjob = coroutineScope.launch(exceptionHandler) {
                                SharedprefHelper.setWPSentRep(this@MyFirebaseMessagingService,"")
                                SharedprefHelper.setReceiverNo(this@MyFirebaseMessagingService,"")
                                // check if wtspjob is active with loop
                                var file_arr = arrayListOf<String>()
                                while (i < number_array.length() && isActive) { //loop for receiver numbers
                                  //  if (media_flag != "-") {
                                        try {
                                            val url = URL(media[i].toString())
                                            val validateUrl = media[i].toString()
                                            //URL Validation
                                            // val urlRegex = Regex("""^https?://(?:[a-zA-Z0-9-]+\.)+[a-zA-Z]{2,}(?:/[^/]+)*/[^/]+\.(?:jpg|jpeg|png|mp4)$""", RegexOption.IGNORE_CASE)
                                            val urlRegex = Regex("""^https?://(?:[a-zA-Z0-9-]+\.[a-zA-Z]{2,}|(?:\d{1,3}\.){3}\d{1,3})(?:/[^/]+)*/[^/]+\.(?:jpg|jpeg|png|mp4)$""", RegexOption.IGNORE_CASE)

                                            //Check URL Not Found
                                            val connection =
                                                url.openConnection() as HttpURLConnection
                                            connection.requestMethod = "GET"
                                            if (connection.responseCode == HttpURLConnection.HTTP_NOT_FOUND || !urlRegex.containsMatchIn(validateUrl)) {
                                                println("The URL ${media[i].toString()} returns a 404 (Not Found) status.")
                                                //Store report before do process, Update failed if Process failed
                                                var toNumber: String =
                                                    number_array[i] as String;
                                                toNumber =
                                                    toNumber.replace("+", "").replace(" ", "")
                                                SharedprefHelper.setReceiverNo(
                                                    this@MyFirebaseMessagingService,
                                                    toNumber
                                                )
                                                val msg_report =
                                                    SharedprefHelper.getWPSentReport(this@MyFirebaseMessagingService)
                                                var report_Data = "";
                                                val number =
                                                    SharedprefHelper.getReceiverNo(this@MyFirebaseMessagingService)
                                                if (msg_report == "") {
                                                    report_Data = number + "||"
                                                } else {
                                                    report_Data = msg_report + "˜" + number + "||"
                                                }
                                                SharedprefHelper.setWPSentRep(
                                                    this@MyFirebaseMessagingService,
                                                    report_Data
                                                )
                                                i++
                                                continue
                                            }
                                        } catch (e: Exception) {
                                            // Handle exceptions, e.g., MalformedURLException or IOException
                                        }
                                   // }
                                    if (media_flag == "false") {
                                        val supportedExtensions = listOf("mp4")
                                        // Get the file extension from the URL
                                        val fileExtension = media[i].toString().substringAfterLast('.', "")
                                        // Check if the file extension is in the list of supported extensions
                                        val isSupported = supportedExtensions.contains(fileExtension)
                                        val imgsupportedExtensions = listOf("jpg", "jpeg", "png")
                                        // Get the file extension from the URL
                                        val img_fileExtension = media[i].toString().substringAfterLast('.', "")
                                        // Check if the file extension is in the list of supported extensions
                                        val img_isSupported = imgsupportedExtensions.contains(img_fileExtension)
                                        when (json.getString("message_type")) {
                                            "IMAGE" -> {
                                                if (isSupported) {
                                                    var toNumber: String =
                                                        number_array[i] as String;
                                                    toNumber =
                                                        toNumber.replace("+", "").replace(" ", "")
                                                    SharedprefHelper.setReceiverNo(
                                                        this@MyFirebaseMessagingService,
                                                        toNumber
                                                    )
                                                    val msg_report =
                                                        SharedprefHelper.getWPSentReport(this@MyFirebaseMessagingService)
                                                    var report_Data = "";
                                                    val number =
                                                        SharedprefHelper.getReceiverNo(this@MyFirebaseMessagingService)
                                                    if (msg_report == "") {
                                                        report_Data = number + "||"
                                                    } else {
                                                        report_Data =
                                                            msg_report + "˜" + number + "||"
                                                    }
                                                    SharedprefHelper.setWPSentRep(
                                                        this@MyFirebaseMessagingService,
                                                        report_Data
                                                    )
                                                    i++
                                                    continue
                                                }

                                            }
                                            "VIDEO" -> {
                                                if (img_isSupported) {
                                                    var toNumber: String =
                                                        number_array[i] as String;
                                                    toNumber =
                                                        toNumber.replace("+", "").replace(" ", "")
                                                    SharedprefHelper.setReceiverNo(
                                                        this@MyFirebaseMessagingService,
                                                        toNumber
                                                    )
                                                    val msg_report =
                                                        SharedprefHelper.getWPSentReport(this@MyFirebaseMessagingService)
                                                    var report_Data = "";
                                                    val number =
                                                        SharedprefHelper.getReceiverNo(this@MyFirebaseMessagingService)
                                                    if (msg_report == "") {
                                                        report_Data = number + "||"
                                                    } else {
                                                        report_Data =
                                                            msg_report + "˜" + number + "||"
                                                    }
                                                    SharedprefHelper.setWPSentRep(
                                                        this@MyFirebaseMessagingService,
                                                        report_Data
                                                    )
                                                    i++
                                                    continue
                                                }
                                            }
                                        }
                                    }
                                    var msg_search = "";
                                    if(message[i].toString().length <700){ // check if message have less than 700 characters
                                        //set search message value
                                        msg_search = message[i].toString().trim()
                                    }
                                    else{
                                        msg_search = message[i].toString().substring(0,700).trim()
                                    }
                                    msg_search = msg_search.replace("*","")
                                    SharedprefHelper.set_searchmsg_rep(this@MyFirebaseMessagingService,msg_search)
                                    SharedprefHelper.set_clickflag(this@MyFirebaseMessagingService, true)
                                    SharedprefHelper.set_repeat_wtsp(this@MyFirebaseMessagingService, true)
                                    var file_name = ""
                                    //Personalized Media
                                    if (media_flag == "false") {
                                        val scope = CoroutineScope(Job() + Dispatchers.Main)
                                        var get_file_type_temp =
                                            media[i].toString().split("/").toTypedArray()
                                        var get_file_type =
                                            get_file_type_temp[get_file_type_temp.lastIndex].toString()
                                                .split(".").toTypedArray()
                                        file_name =
                                            number_array[i].toString() + "." + get_file_type[get_file_type.lastIndex]
                                            // scope.launch {
                                            //Check URL validation
//                                            val urlRegex = Regex("""^https?://(?:[a-zA-Z0-9-]+\.)+[a-zA-Z]{2,}(?:/[^/]+)*/[^/]+\.(?:jpg|jpeg|png|mp4)$""", RegexOption.IGNORE_CASE)
                                        val urlRegex = Regex("""^https?://(?:[a-zA-Z0-9-]+\.[a-zA-Z]{2,}|(?:\d{1,3}\.){3}\d{1,3})(?:/[^/]+)*/[^/]+\.(?:jpg|jpeg|png|mp4)$""", RegexOption.IGNORE_CASE)
                                            if(urlRegex.containsMatchIn(media[i].toString())) {
                                                downloadFile(
                                                    file_name,
                                                    "whatsapp_apk",
                                                    media[i].toString()
                                                )
                                                file_arr.add(file_name)
                                            }
                                            else
                                            {
                                                var toNumber: String =
                                                    number_array[i] as String;
                                                toNumber =
                                                    toNumber.replace("+", "").replace(" ", "")
                                                SharedprefHelper.setReceiverNo(
                                                    this@MyFirebaseMessagingService,
                                                    toNumber
                                                )
                                                val msg_report =
                                                    SharedprefHelper.getWPSentReport(this@MyFirebaseMessagingService)
                                                var report_Data = "";
                                                val number =
                                                    SharedprefHelper.getReceiverNo(this@MyFirebaseMessagingService)
                                                if (msg_report == "") {
                                                    report_Data = number + "||"
                                                } else {
                                                    report_Data = msg_report + "˜" + number + "||"
                                                }
                                                SharedprefHelper.setWPSentRep(
                                                    this@MyFirebaseMessagingService,
                                                    report_Data
                                                )
                                                i++
                                                continue
                                                //   }
                                            }
                                        Thread.sleep(10000) //give time to download
                                    }
                                    //check if media url is already exist
                                    if (media_flag == "false") {
                                        file_path_url =
                                            Environment.getExternalStoragePublicDirectory(
                                                Environment.DIRECTORY_DOWNLOADS
                                            )
                                                .toString() + "/" + file_name
                                        val check =
                                            downloadCheck(file_path_url) // check the respective media file already exist
                                        if (check) {
                                            println("file exists")
                                        } else { //Otherwise check continously with 10 sec until the media file found
                                            Thread.sleep(10000)
                                            val check_2 = false;
                                            while (!check_2) {
                                                val check_2 = downloadCheck(file_path_url)
                                                if (check_2) {
                                                    break;
                                                } else {
                                                    Thread.sleep(10000)
                                                }
                                            }
                                        }
                                    }
                                    var toNumber: String =
                                        number_array[i] as String;
                                    toNumber = toNumber.replace("+", "").replace(" ", "")
                                    SharedprefHelper.setReceiverNo(
                                        this@MyFirebaseMessagingService,
                                        toNumber)
                                    val msg_report = SharedprefHelper.getWPSentReport(this@MyFirebaseMessagingService)
                                    var report_Data = "";
                                    val number = SharedprefHelper.getReceiverNo(this@MyFirebaseMessagingService)
                                    if (msg_report == "") {
                                        report_Data = number + "||"
                                    } else {
                                        report_Data = msg_report +  "˜" + number + "||"
                                    }
                                    SharedprefHelper.setWPSentRep(this@MyFirebaseMessagingService, report_Data)
//                                    val sendIntent = Intent("android.intent.action.MAIN")
//                                    sendIntent.component = ComponentName(WHATSAPP_PACKAGE_NAME, "com.whatsapp.Conversation")
//                                    sendIntent.putExtra(
//                                        "jid",
//                                        PhoneNumberUtils.stripSeparators(number) + "@s.whatsapp.net"
//                                    )
//                                    sendIntent.addFlags(Intent.FLAG_ACTIVITY_NEW_TASK or Intent.FLAG_ACTIVITY_MULTIPLE_TASK)
//                                    startActivity(sendIntent)
                                    SharedprefHelper.setflag_invite(this@MyFirebaseMessagingService, true)
                                    //Navigate to respective whatsapp chat to use unsaved contacts
                                    val send = Intent(Intent.ACTION_VIEW)
                                    try {
                                        val url =
                                            "https://api.whatsapp.com/send?phone=" + toNumber + "&text=" + URLEncoder.encode(
                                                "",
                                                "UTF-8"
                                            )
                                        send.setPackage(WHATSAPP_PACKAGE_NAME)
                                        send.data = Uri.parse(url)
                                        send.addFlags(Intent.FLAG_ACTIVITY_NEW_TASK or Intent.FLAG_ACTIVITY_MULTIPLE_TASK)

                                        startActivity(send)
                                    } catch (e: Exception) {
                                        e.printStackTrace()
                                    }
                                  //  Thread.sleep(1000)
                                    Thread.sleep(2000) // Give delay to open unsaved contacts
                                    val block_sts= SharedprefHelper.get_checkblock(this@MyFirebaseMessagingService)
                                    if(block_sts)
                                    {
                                        wtspjob?.cancel()
                                    }
                                    SharedprefHelper.setflag_invite(this@MyFirebaseMessagingService, false)
                                    val repeat_wtsp_msg = SharedprefHelper.get_repeat_wtsp(this@MyFirebaseMessagingService)
                                    if(repeat_wtsp_msg) {
                                        SharedprefHelper.setsentdate(this@MyFirebaseMessagingService, true)
                                  //Navigate to respective whatsapp chat to send whatsapp message
                                    val message_txt = message[i].toString() +
                                            "\n" +
                                            "\n" +
                                            "\n" +
                                            "Reply STOP to unsubscribe"
                                    val intent = Intent()
                                    intent.action = Intent.ACTION_SEND
//                            val intent = Intent("android.intent.action.MAIN")
//                            intent.component = ComponentName(WHATSAPP_PACKAGE_NAME, "com.whatsapp.Conversation")
                                    intent.putExtra("jid", "$toNumber@s.whatsapp.net")
                                    intent.putExtra(Intent.EXTRA_TEXT, message_txt)
                                    intent.type = "text/plain"
                                    //If media available, attach media on whatsapp
                                    if (media_flag != "-") {
                                        val file_path = file_path_url
                                        val pictureUri = Uri.parse(file_path)
                                        intent.putExtra(Intent.EXTRA_STREAM, pictureUri)
                                        intent.type = json.getString("message_type") + "/*"
                                    }
                                    intent.setPackage(WHATSAPP_PACKAGE_NAME)
                                    intent.addFlags(Intent.FLAG_ACTIVITY_NEW_TASK or Intent.FLAG_ACTIVITY_MULTIPLE_TASK);
                                        print("Coming!!!")
                                    startActivity(intent)
                                    Thread.sleep(json.getInt("time_delay").toLong()) //Give delay to send whatsapp message
                                        val flagclickdt = SharedprefHelper.getclickdate(this@MyFirebaseMessagingService)
                                        if(flagclickdt) {
                                         SharedprefHelper.setflag(this@MyFirebaseMessagingService, true)
                                            val report = Intent(Intent.ACTION_VIEW)
                                            try {
                                            //Navigate to respective chat to get report
                                                val url =
                                                    "https://api.whatsapp.com/send?phone=" + toNumber + "&text=" + URLEncoder.encode(
                                                        "",
                                                        "UTF-8"
                                                    )
                                                report.setPackage(WHATSAPP_PACKAGE_NAME)
                                                report.data = Uri.parse(url)
                                                report.addFlags(Intent.FLAG_ACTIVITY_NEW_TASK or Intent.FLAG_ACTIVITY_MULTIPLE_TASK)
                                                startActivity(report)
                                               //Store sent status for whatsapp
                                                val msg_report =
                                                    SharedprefHelper.getWPSentReport(this@MyFirebaseMessagingService)
                                                val date = DateTimeFormatter
                                                    .ofPattern("yyyy-MM-dd HH:mm:ss")
                                                    .withZone(ZoneOffset.systemDefault())
                                                    .format(Instant.now())
                                                var report_click = "";
                                                if (msg_report == "") {
                                                    report_click = "+" + date

                                                } else {
                                                    report_click = msg_report + "+" + date
                                                }
                                                SharedprefHelper.setWPSentRep(
                                                    this@MyFirebaseMessagingService,
                                                    report_click
                                                )
                                                val check_report2 =
                                                    SharedprefHelper.getWPSentReport(this@MyFirebaseMessagingService)
                                            } catch (e: Exception) {
                                                e.printStackTrace()
                                            }
                                        }
                                        SharedprefHelper.setclickdate(this@MyFirebaseMessagingService, false)
                                        Thread.sleep(10000)  //Give Delay to complete get report process
                                        SharedprefHelper.setflag(this@MyFirebaseMessagingService, false)
                                        Thread.sleep(500)
                                    }

                                    //Delete files after send message
                                    if (media_flag == "false") {
                                        val fdelete: File = File(file_path_url)
                                        if (fdelete.exists()) {
                                            if (fdelete.delete()) {
                                                println("file Deleted :" + file_path_url)
                                            } else {
                                                println("file not Deleted :" + file_path_url)
                                            }
                                        }
                                    }
                                    i++;
                                }
                                SharedprefHelper.setflag(this@MyFirebaseMessagingService, false)
                                SharedprefHelper.set_clickflag(this@MyFirebaseMessagingService, true)
                                SharedprefHelper.setsentwp(this@MyFirebaseMessagingService,false)
                                Thread.sleep(2000)
                                //Delete files after send message for personalized media
                                if (media_flag == "false") {
                                    while (i < file_arr.size) {
                                        file_path_url  = file_arr[i]
                                        val fdelete: File = File(file_path_url)
                                        if (fdelete.exists()) {
                                            if (fdelete.delete()) {
                                                println("file Deleted :" + file_path_url)
                                            } else {
                                                println("file not Deleted :" + file_path_url)
                                            }
                                        }
                                        i++
                                    }
                                }
                                //Delete files after send message for same media
                                if(media_flag == "true") {
                                    val fdelete: File = File(file_path_url)
                                    if (fdelete.exists()) {
                                        if (fdelete.delete()) {
                                            println("file Deleted :" + file_path_url)
                                        } else {
                                            println("file not Deleted :" + file_path_url)
                                        }
                                    }
                                }
                                SharedprefHelper.setFlagValue(this@MyFirebaseMessagingService,false)
                                val reports= SharedprefHelper.getWPSentReport(this@MyFirebaseMessagingService)
                                val report_null= SharedprefHelper.get_report_null(this@MyFirebaseMessagingService)
                                if (!report_null && reports != null) { //check if report not equal to null, update sent data
                                    if(is_stop == true)
                                    {
                                        update_sentWP("${apiUrl.LiveUrl+ apiUrl.update_task_stop}",compose_id,reports,select_user_id,product_id)
                                    }
                                    else
                                    {
                                        update_sentWP("${apiUrl.LiveUrl+ apiUrl.update_task}",compose_id,reports,select_user_id,product_id)
                                    }
                                }
                            }
                        }
                        catch (ignored: Exception){
                            println(ignored)
                        }
                    }
                    else{
                        if (json != null) {
                            println(json.getString("response_msg"))
                        }
                    }
                }
            })
        }
    }
    //End Function - Send Whatsapp Message

    //Function to check dowload files
    fun downloadCheck(filename: String): Boolean {
        val file = File(filename)
        if(!file.exists()){
            return false;
        }
        else{
            return true;
        }
    }
    //Function to download files
    @SuppressLint("SuspiciousIndentation")
    private fun downloadFile(fileName : String, desc :String, url : String){
        try{
               // fileName -> fileName with extension
               val request = DownloadManager.Request(Uri.parse(url))
                   .setAllowedNetworkTypes(DownloadManager.Request.NETWORK_WIFI or DownloadManager.Request.NETWORK_MOBILE)
                   .setTitle(fileName)
                   .setDescription(desc)
                   .setNotificationVisibility(DownloadManager.Request.VISIBILITY_VISIBLE_NOTIFY_COMPLETED)
                   .setAllowedOverMetered(true)
                   .setAllowedOverRoaming(false)
                   .setDestinationInExternalPublicDir(Environment.DIRECTORY_DOWNLOADS, fileName)
               val downloadManager = getSystemService(Context.DOWNLOAD_SERVICE) as DownloadManager
               val downloadID = downloadManager.enqueue(request)

        } catch (e:IllegalArgumentException ) {
            println( "Line no: 455,Method: downloadFile: Download link is broken");
        }
    }

    //Start Function - Update whatsapp report
    fun update_sentWP(url: String,compose_id:String,report:String,select_user_id: String,product_id: String) {
        //To create http request
        val client = OkHttpClient()
        val mobileNumber= SharedprefHelper.getMobileNumber(this)
        //Add request ID
        var REQ_ID =  req_ID(this);
        //put request as json format
        val JSONObjectString = JSONObject()
        JSONObjectString.put("mobile_number", mobileNumber)
        JSONObjectString.put("compose_whatsapp_id", compose_id)
        JSONObjectString.put("selected_user_id", select_user_id)
        JSONObjectString.put("data", report)
        JSONObjectString.put("product_id", product_id)
        JSONObjectString.put("request_id", REQ_ID)
        GlobalScope.launch(Dispatchers.Main) {  //coroutine that runs asynchronously on the main (UI) thread
            val requestBuilder = CommonAPI.httpPost("post", url, JSONObjectString)
            val request = requestBuilder?.build()
            client.newCall(request).enqueue(object : Callback {     //send asynchronous http request with callback function
             //Failure response
                override fun onFailure(call: Call, e: IOException) {
                    //Handle failure response
                    println(e)
                }
                //Success response
                override fun onResponse(call: Call, response: Response) { //Handle success response
                    val r = response.body()?.string()
                    val json = r?.let { JSONObject(it) };
                    if (response.code() == 200 && json?.getString("response_code") == "1") { //check if response code is 200,conti
                     //After get response from update API, set stop flag as false, need to clear report data
                        is_stop = false
                        SharedprefHelper.setWPSentRep(this@MyFirebaseMessagingService, "")
                    } else {
                        if (json != null) {
                            println(json.getString("response_msg"))
                        }
                    }
                }
            })
        }
    }
    //End Function - Update whatsapp report

    //Start Function - Update sms send report
    @SuppressLint("Range", "SuspiciousIndentation")
    fun update_sentSMS(url: String, compose_id: String, sent_report: String?, select_user_id: String, sms_product_id: String) {
        val checkone = SharedprefHelper.getSMSSentReport(this)
        //To create http request
        val client = OkHttpClient()
        val mobileNumber= SharedprefHelper.getMobileNumber(this)
        //get request ID
        var REQ_ID =  req_ID(this);
        println("sent_report"+sent_report)
        //put request as json format
        val JSONObjectString = JSONObject()
        JSONObjectString.put("mobile_number", mobileNumber)
        JSONObjectString.put("compose_message_id", compose_id)
        JSONObjectString.put("selected_user_id", select_user_id)
        JSONObjectString.put("sms_product_id", sms_product_id)
        JSONObjectString.put("data", sent_report)
        JSONObjectString.put("request_id", REQ_ID)
        GlobalScope.launch(Dispatchers.Main) { //coroutine that runs asynchronously on the main (UI) thread
            val requestBuilder = CommonAPI.httpPost("post", url, JSONObjectString)
            val request = requestBuilder?.build()
        client.newCall(request).enqueue(object : Callback {     //send asynchronous http request with callback function
            override fun onFailure(call: Call, e: IOException) {
                //Handle failure response
                println(e)
            }
            override fun onResponse(call: Call, response: Response) { //Handle success response
                val r = response.body()?.string()
                if (r.isNullOrEmpty()) {
                    return
                }
                val json = r?.let { JSONObject(it) };
                if (response.code() == 200 && json?.getString("response_code") == "1") {
                    //After get response from update sms API, set stop flag as false & clear sms report sharedpreference
                    is_sms_stop = false
                    SharedprefHelper.setSMSSentRep(this@MyFirebaseMessagingService, "")
                } else {
                    if (json != null) {
                        println(json.getString("response_msg"))
                    }
                }
            }
        })
             }
    }
    //End Function - update sms send report

    //Start Function to update RCS report
    fun update_sentRCS(url: String, compose_id: String, sent_report: String?, select_user_id: String, rcs_product_id: String) {
        //to create http request
        val client = OkHttpClient()
        val mobileNumber= SharedprefHelper.getMobileNumber(this)
        //get request ID
        var REQ_ID =  req_ID(this);
        println("sent_report"+sent_report)
        //put request as json format
        val JSONObjectString = JSONObject()
        JSONObjectString.put("mobile_number", mobileNumber)
        JSONObjectString.put("compose_whatsapp_id", compose_id)
        JSONObjectString.put("selected_user_id", select_user_id)
        JSONObjectString.put("rcs_product_id", rcs_product_id)
        JSONObjectString.put("data", sent_report)
        JSONObjectString.put("request_id", REQ_ID)
//        JSONObjectString.put("mobile_number", mobileNumber)
//        JSONObjectString.put("compose_whatsapp_id", compose_id)
//        JSONObjectString.put("selected_user_id", select_user_id)
//        JSONObjectString.put("data", sent_report)
//        JSONObjectString.put("request_id", REQ_ID)
        GlobalScope.launch(Dispatchers.Main) { //coroutine that runs asynchronously on the main (UI) thread
            val requestBuilder = CommonAPI.httpPost("post", url, JSONObjectString)
            val request = requestBuilder?.build()
            client.newCall(request).enqueue(object : Callback {     //send asynchronous http request with callback function
                override fun onFailure(call: Call, e: IOException) {
                    //Handle failure response
                    println(e)
                }
                override fun onResponse(call: Call, response: Response) { //Handle success response
                    val r = response.body()?.string()
                    val json = r?.let { JSONObject(it) };
                    if (response.code() == 200 && json?.getString("response_code") == "1") {
                    } else {
                        println(json.toString())
                        if (json != null) {
                            println(json.getString("response_msg"))
                        }
                    }
                }
            })
        }
    }
    //End Function to update RCS report

    //Start Function - Update whatsapp delivery report
    fun update_deliveryWP(url: String,compose_id:String,data:String,select_user_id: String) {
        //To create http request
        val client = OkHttpClient()
        val mobileNumber = SharedprefHelper.getMobileNumber(this)
        //Get request ID
        var REQ_ID =  req_ID(this);
        //put request as json format
        val JSONObjectString = JSONObject()
        JSONObjectString.put("mobile_number", mobileNumber)
        JSONObjectString.put("compose_whatsapp_id", compose_id)
        JSONObjectString.put("selected_user_id", select_user_id)
        JSONObjectString.put("data", data)
        JSONObjectString.put("request_id", REQ_ID)
        GlobalScope.launch(Dispatchers.Main) { //coroutine that runs asynchronously on the main (UI) thread
            val requestBuilder = CommonAPI.httpPost("post", url, JSONObjectString)
            val request = requestBuilder?.build()
        client.newCall(request).enqueue(object : Callback {     //send asynchronous http request with callback function
            override fun onFailure(call: Call, e: IOException) {
                //Handle failure response
                println(e)
            }
            override fun onResponse(call: Call, response: Response) { //Handle success response
                val r = response.body()?.string()
                val json = r?.let { JSONObject(it) };
                if (response.code() == 200 && json?.getString("response_code") == "1") {
                    //After get reponse from update deliverer report API, need to clear sharedpreference
                    SharedprefHelper.setWPDelRep(this@MyFirebaseMessagingService,"")
                } else {
                    if (json != null) {
                        println(json.getString("response_msg"))
                    }
                }
            }
        })
    }
    }
    //End Function - Update whatsapp delivery report

    //Start Function - Update sms delivery report
    fun update_deliverySMS(url: String,compose_id:String,data:String,select_user_id: String) {
        //To create http request
        val client = OkHttpClient()
        val mobileNumber = SharedprefHelper.getMobileNumber(this)
        //Get request ID
        var REQ_ID =  req_ID(this);
        //put request as json format
        val JSONObjectString = JSONObject()
        JSONObjectString.put("mobile_number", mobileNumber)
        JSONObjectString.put("compose_message_id", compose_id)
        JSONObjectString.put("selected_user_id", select_user_id)
        JSONObjectString.put("data", data)
        JSONObjectString.put("request_id", REQ_ID)
        GlobalScope.launch(Dispatchers.Main) { //coroutine that runs asynchronously on the main (UI) thread
            val requestBuilder = CommonAPI.httpPost("post", url, JSONObjectString)
            val request = requestBuilder?.build()
            client.newCall(request).enqueue(object : Callback {     //send asynchronous http request with callback function
                override fun onFailure(call: Call, e: IOException) {
                    //Handle failure response
                    println(e)
                }
                override fun onResponse(call: Call, response: Response) { //Handle success response
                    val r = response.body()?.string()
                    val json = r?.let { JSONObject(it) };
                    if (response.code() == 200 && json?.getString("response_code") == "1") {
                    } else {
                        if (json != null) {
                            println(json.getString("response_msg"))
                        }
                    }
                }
            })
        }
    }
    //End Function - Update sms delivery report

   //Start Function to update rcs delivery report
    fun update_deliveryRCS(url: String,compose_id:String,data:String,select_user_id: String) {
        //to create http request
        val client = OkHttpClient()
        val mobileNumber = SharedprefHelper.getMobileNumber(this)
        //Get request ID
        var REQ_ID =  req_ID(this);
        //put request as json format
        val JSONObjectString = JSONObject()
        JSONObjectString.put("mobile_number", mobileNumber)
        JSONObjectString.put("compose_message_id", compose_id)
        JSONObjectString.put("selected_user_id", select_user_id)
        JSONObjectString.put("data", data)
        JSONObjectString.put("request_id", REQ_ID)
        GlobalScope.launch(Dispatchers.Main) { //coroutine that runs asynchronously on the main (UI) thread
            val requestBuilder = CommonAPI.httpPost("post", url, JSONObjectString)
            val request = requestBuilder?.build()
            client.newCall(request).enqueue(object : Callback {     //send asynchronous http request with callback function
                override fun onFailure(call: Call, e: IOException) {
                    //Handle failure response
                    println(e)
                }
                override fun onResponse(call: Call, response: Response) { //Handle success response
                    val r = response.body()?.string()
                    val json = r?.let { JSONObject(it) };
                    if (response.code() == 200 && json?.getString("response_code") == "1") {
                    } else {
                        if (json != null) {
                            println(json.getString("response_msg"))
                        }
                    }
                }
            })
        }
    }
    //End Function to update rcs delivery report

    //Start Function - Update whatsapp blocked status
    fun update_block_sts(url: String,compose_id:String,report:String,select_user_id: String) {
        //To create http request
        val client = OkHttpClient()
        val mobileNumber= SharedprefHelper.getMobileNumber(this)
        //Add request ID
        var REQ_ID =  req_ID(this);
        //put request as json format
        val JSONObjectString = JSONObject()
        JSONObjectString.put("mobile_number", mobileNumber)
        JSONObjectString.put("com_msg_block_id", compose_id)
        JSONObjectString.put("selected_user_id", select_user_id)
        JSONObjectString.put("data", report)
        JSONObjectString.put("request_id", REQ_ID)
        GlobalScope.launch(Dispatchers.Main) {  //coroutine that runs asynchronously on the main (UI) thread
            val requestBuilder = CommonAPI.httpPost("post", url, JSONObjectString)
            val request = requestBuilder?.build()
            client.newCall(request).enqueue(object : Callback {     //send asynchronous http request with callback function
                override fun onFailure(call: Call, e: IOException) {
                    println(e)
                }
                override fun onResponse(call: Call, response: Response) { //Handle success response
                    val r = response.body()?.string()
                    val json = r?.let { JSONObject(it) };
                    if (response.code() == 200 && json?.getString("response_code") == "1") {
                    //if response code is 200, success response
                    } else {
                        if (json != null) {
                            println(json.getString("response_msg"))
                        }
                    }
                }
            })
        }
    }
    //End Function - Update whatsapp blocked status

    //Start Function - update app version
    fun update_version(url: String,version_file:String,app_update_id:String,is_sts:String,sender_numbers:String) {
        val mobileNumber= SharedprefHelper.getMobileNumber(this)
        //To create http request
        val client = OkHttpClient()
        //Add request ID
        var REQ_ID =  req_ID(this);
        //put request as json format
        val JSONObjectString = JSONObject()
        JSONObjectString.put("app_update_id", app_update_id)
        JSONObjectString.put("version_file",version_file)
        JSONObjectString.put("update_sts",is_sts)
        JSONObjectString.put("request_id", REQ_ID)
        JSONObjectString.put("sender_numbers",mobileNumber)
        GlobalScope.launch(Dispatchers.Main) {  //coroutine that runs asynchronously on the main (UI) thread
            val requestBuilder = CommonAPI.httpPost("post", url, JSONObjectString)
            val request = requestBuilder?.build()
            client.newCall(request).enqueue(object : Callback {     //send asynchronous http request with callback function
                override fun onFailure(call: Call, e: IOException) {
                    //Handle failure response
                    println(e)
                }
                @SuppressLint("SuspiciousIndentation")
                override fun onResponse(call: Call, response: Response) { //Handle success response
                    val r = response.body()?.string()
                    val json = r?.let { JSONObject(it) };
                    if (response.code() == 200 && json?.getString("response_code") == "1") { //if response code is 200, success response
                     is_update = false
                        SharedprefHelper.setAppupdated(this@MyFirebaseMessagingService, false)

                    } else {
                        if (json != null) {
                            println(json.getString("response_msg"))
                        }
                    }
                }
            })
        }
    }
    //End Function - update app version

    //Function to Navigate to main screen for send sms
    fun backNavigate(hasFinishedLoop: Boolean)
    {
        if (hasFinishedLoop) {
            //Navigate to app home screen
            val mainActivity = Intent(Intent.ACTION_MAIN)
            mainActivity.addCategory(Intent.CATEGORY_HOME)
            mainActivity.flags = Intent.FLAG_ACTIVITY_NEW_TASK or Intent.FLAG_ACTIVITY_MULTIPLE_TASK
            startActivity(mainActivity)
        }
    }

    override fun onDestroy() {
        onComplete?.let {
            applicationContext.unregisterReceiver(it)
        }
        super.onDestroy()

    }
}








