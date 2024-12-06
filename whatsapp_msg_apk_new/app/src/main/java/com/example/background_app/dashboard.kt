/*
This kotlin file is used to access dashboard

Version : 1.0
Author : Sabena yasmin (YJ0008)
Date : 28-Mar-2024
*/

package com.example.background_app

// Import the required packages and libraries
import android.annotation.SuppressLint
import android.app.ProgressDialog
import android.content.*
import android.net.Uri
import android.os.Build
import android.os.Bundle
import android.provider.Settings
import android.util.TypedValue
import android.view.Gravity
import android.widget.Button
import android.widget.TextView
import androidx.annotation.RequiresApi
import androidx.appcompat.app.AlertDialog
import androidx.appcompat.app.AppCompatActivity
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.GlobalScope
import kotlinx.coroutines.launch
import okhttp3.*
import org.json.JSONObject
import java.io.IOException

// Start Process - DashboardActivity
class DashboardActivity : AppCompatActivity() {
    //Set flag for App update status
    var is_appUpdated = false
    //Get stop campaign status using broadcast receiver
    private val stopCampaignReceiver = object : BroadcastReceiver() {
        override fun onReceive(context: Context?, intent: Intent?) {
          //Display dialog to verify campaign stopped
            showStopCampaignDialog()
        }
    }
    //To create http request
    private val client = OkHttpClient()
    @RequiresApi(Build.VERSION_CODES.S)
    @SuppressLint("MissingInflatedId", "QueryPermissionsNeeded", "Range", "SuspiciousIndentation")
    override fun onCreate(savedInstanceState: Bundle?) {
        // Register the BroadcastReceiver to receive the broadcast
        val filter = IntentFilter("SHOW_STOP_CAMPAIGN_DIALOG")
        registerReceiver(stopCampaignReceiver, filter)
        super.onCreate(savedInstanceState)
        //View dashboard page
        setContentView(R.layout.dashboard)
        //Logout button
        val logout_button = findViewById(R.id.logoutButton) as Button
        //Display app version details
        val AppText = findViewById<TextView>(R.id.apptext)
        AppText.text = "MessageApp"
        val versionText = findViewById<TextView>(R.id.versiontext)
        versionText.text = "Version 5.0"
        SharedprefHelper.setVersion(this@DashboardActivity, versionText.text.toString())
         is_appUpdated= SharedprefHelper.getAppupdated(this)
        val app_title= SharedprefHelper.getTitle(this)
        val app_updated_id= SharedprefHelper.getAppupdateID(this)
        val app_sender_numbers= SharedprefHelper.getSenderNumbers(this)
          //Check app updated status
            if(is_appUpdated == true)
            {
                update_version("${apiUrl.LiveUrl+ apiUrl.update_task_version}",app_title,app_updated_id,"1",app_sender_numbers)
            }
            else
            {
                println("update data..")
            }
     //Logout process
        logout_button.setOnClickListener {
            val builder = AlertDialog.Builder(this@DashboardActivity)
            builder.setMessage("Are you sure you want to logout?")
                .setCancelable(false)
                .setPositiveButton("Yes") { dialog, id ->
                    mobilelogout("${apiUrl.LiveUrl + apiUrl.logout}")
                }
                .setNegativeButton("No") { dialog, id ->
                    dialog.dismiss()
                }
            val alert = builder.create()
            alert.show()
        }
    }
    //Start Function - Update App Version
    fun update_version(
        url: String,
        version_file: String?,
        app_update_id: String?,
        is_sts:String,
        sender_numbers: String?
    ) {
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
        println(JSONObjectString);
        GlobalScope.launch(Dispatchers.Main) {  //coroutine that runs asynchronously on the main (UI) thread
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
                    if (response.code() == 200 && json?.getString("response_code") == "1") { //if response code is 200, success response
                       //After app gets updated, need to set status as false
                        is_appUpdated = false
                        SharedprefHelper.setAppupdated(this@DashboardActivity, false)

                    } else {
                        if (json != null) {
                            println(json.getString("response_msg"))
                        }
                    }
                }
            })
        }
    }
    //End Function - Update App Version

    //Start Function - Show stop campaign dialog
    private fun showStopCampaignDialog() {
        val builder = AlertDialog.Builder(this)
        val message = TextView(this)
        message.text = "Stopped Running Campaign."
        message.gravity = Gravity.CENTER
        message.setTextSize(TypedValue.COMPLEX_UNIT_SP, 16f)
        message.setPadding(16, 30, 16, 20)
        builder.setView(message)
        builder.setCancelable(false)
            .setPositiveButton("Ok") { dialog, id ->
            }
        val alert = builder.create()
        alert.show()
    }
    //End Function - Show stop campaign dialog

//Use onDestroy for register to avoid memory leak
    override fun onDestroy() {
        // Unregister the BroadcastReceiver to avoid memory leaks
        unregisterReceiver(stopCampaignReceiver)
        super.onDestroy()
    }

 //Start Function - Mobile Logout
    fun mobilelogout(url: String) {
        val mProgressDialog = ProgressDialog(this@DashboardActivity, R.style.ProgressBarStyle)
        mProgressDialog.setMessage("Loading...")
        mProgressDialog.show()
        val mobileNumber = SharedprefHelper.getMobileNumber(this)
        //Add request ID
        var REQ_ID =  req_ID(this);
        //put request as json format
        val JSONObjectString = JSONObject()
        JSONObjectString.put("mobile_number", mobileNumber)
        JSONObjectString.put("request_id", REQ_ID)
        GlobalScope.launch(Dispatchers.Main) {  //coroutine that runs asynchronously on the main (UI) thread
            val requestBuilder = CommonAPI.httpPost("post", url, JSONObjectString)  //send request commonAPI function and process the response
            val request = requestBuilder?.build()
            //send asynchronous http request with callback function
            client.newCall(request).enqueue(object : Callback {
                override fun onFailure(call: Call, e: IOException) {
                    //Handle failure response
                    println(e)
                    //Run a block of code on the main (UI) thread
                    runOnUiThread {
                        val builder = AlertDialog.Builder(this@DashboardActivity)
                        val message = TextView(this@DashboardActivity)
                        message.text = "Something went wrong. \nPlease try again after sometime."
                        message.gravity = Gravity.CENTER
                        message.setTextSize(TypedValue.COMPLEX_UNIT_SP, 16f)
                        message.setPadding(16, 30, 16, 20)
                        builder.setView(message)
                        builder.setCancelable(false)
                            .setPositiveButton("Ok") { dialog, id ->
                                mProgressDialog.dismiss()
                                mobilelogout("${apiUrl.LiveUrl + apiUrl.logout}")
                            }
                            .setNegativeButton("Cancel") { dialog, id ->
                                // Dismiss the dialog
                                dialog.dismiss()
                                mProgressDialog.dismiss()
                            }
                        val alert = builder.create()
                        alert.show()

                    }
                }
                override fun onResponse(call: Call, response: Response) {
                    //Handle success response
                    val r = response.body()?.string()
                    val json = r?.let { JSONObject(it) };
                    if (response.code() == 200 && json?.getString("response_code") == "1") { //check if response code is 200, success response
                        runOnUiThread {  //afely perform UI-related operations on the main thread
                            mProgressDialog.dismiss()
                            //Set login value
                            SharedprefHelper.setLogin(this@DashboardActivity, false)
                            SharedprefHelper.setMobileNumber(this@DashboardActivity, "")
                            val intent = Intent(this@DashboardActivity, MainActivity::class.java)
                            startActivity(intent)
                            // Clear All SharedPreferences
                            val sharedPreferences = getSharedPreferences("YOUR_PREFERENCE_NAME", Context.MODE_PRIVATE)
                            val editor = sharedPreferences.edit()
                            editor.clear()
                            editor.apply()
                        }
                    } else {
                        runOnUiThread {
                            mProgressDialog.dismiss()
                            if (json != null) {
                                println(json.getString("response_msg"))
                                var err_msg = json?.getString("response_msg")
                                val builder = AlertDialog.Builder(this@DashboardActivity)
                                builder.setMessage("$err_msg")
                                    .setCancelable(false)
                                    .setPositiveButton("Ok") { dialog, id ->
                                        mobilelogout("${apiUrl.LiveUrl + apiUrl.logout}")
                                    }
                                    .setNegativeButton("Cancel") { dialog, id ->
                                        dialog.dismiss()
                                        mProgressDialog.dismiss()
                                    }
                                val alert = builder.create()
                                alert.show()
                            }
                        }
                    }
                }
            })
        }
    }
    //End Function - Mobile Logout
}
