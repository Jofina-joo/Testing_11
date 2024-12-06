/*
This kotlin file is used in mainactivity to access app, permissions

Version : 1.0
Author : Sabena yasmin (YJ0008)
Date : 28-Mar-2024
*/
package com.example.background_app

//import the required packages and files
import android.Manifest
import android.Manifest.permission.WRITE_EXTERNAL_STORAGE
import android.app.ProgressDialog
import android.content.ComponentName
import android.content.Context
import android.content.Intent
import android.content.pm.PackageManager
import android.os.Build
import android.os.Bundle
import android.os.Handler
import android.provider.Settings
import android.text.Editable
import android.util.TypedValue
import android.view.Gravity
import android.view.View
import android.view.inputmethod.InputMethodManager
import android.widget.Button
import android.widget.EditText
import android.widget.TextView
import androidx.appcompat.app.AlertDialog
import androidx.appcompat.app.AppCompatActivity
import androidx.core.app.ActivityCompat
import androidx.core.content.ContextCompat
import com.google.firebase.FirebaseApp
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.GlobalScope
import kotlinx.coroutines.launch
import okhttp3.*
import org.json.JSONObject
import java.io.IOException
import java.util.*
import java.util.regex.Pattern

//call sharedpref and common api function
val SharedprefHelper = Sharedpref()
val CommonAPI = Common_API()

class MainActivity : AppCompatActivity() {
    val apiUrl = ApiUrl() //call apiurl function
    private val REQUEST_SEND_SMS = 123
    val INSTALL_UNKNOWN_APPS_REQUEST_CODE =123
    //To create http request
    private val client = OkHttpClient()
    //Sharedpref for enable permissions one time after installation
    private val PREFS_NAME = "MyAppPrefs"
    private val PREFS_KEY_FUNCTIONS_EXECUTED = "functionsExecuted"

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        FirebaseApp.initializeApp(baseContext); // firebase initialization
        val prefs = getSharedPreferences(PREFS_NAME, Context.MODE_PRIVATE)
        val functionsExecuted = prefs.getBoolean(PREFS_KEY_FUNCTIONS_EXECUTED, false)
        //Set flag to request permissions only after installation
        if (!functionsExecuted) {
            GlobalScope.launch {
                // SMS & Storage Permissions
                request_sms()
                //   autostart()
                autostart_permission()
                //Accept unknown apps
                unknown_apps()
                //Turn off Google play security settings
                security_settings()
            }
            // Set the flag to true to indicate that functions have been executed
            val editor = prefs.edit()
            editor.putBoolean(PREFS_KEY_FUNCTIONS_EXECUTED, true)
            editor.apply()
        }
        var isLogin = SharedprefHelper.getLogin(this@MainActivity)
        if (isLogin) { //check alreadu logged in
            val intent = Intent(this@MainActivity, DashboardActivity::class.java)
            startActivity(intent)
            return;
        } else {//otherwise go to login page
            setContentView(R.layout.login)
        }
        //Login button
        val btn_click_me = findViewById(R.id.loginButton) as Button
        //Give mobie number on textfield
        var mobile_number = findViewById(R.id.username) as EditText
        // set on-click listener
        btn_click_me.setOnClickListener {
            // your code to perform when the user clicks on the button
            if (mobile_number.length() == 0) {
                mobile_number.error = "Mobile Number is required";
                return@setOnClickListener
            } else if (!Pattern.matches("^[0-9]{2}[6-9][0-9]{9}$", mobile_number.text)) {
                mobile_number.error = "Enter valid mobile Number";
                return@setOnClickListener
            }
            fun View.hideKeyboard() {
                val imm =
                    context.getSystemService(Context.INPUT_METHOD_SERVICE) as InputMethodManager
                imm.hideSoftInputFromWindow(windowToken, 0)
            }
            mobile_number.hideKeyboard()
            //Hit Mobile login API
            mobilelogin("${apiUrl.LiveUrl + apiUrl.mobile_login}", mobile_number.text)
        }
    }

    //Start Function - Autostart
    private fun autostart_permission() {
        if (isAutostartAvailable(this@MainActivity)) {
            showAutostartPopup(this@MainActivity)
        }
        else
        {
            runOnUiThread {
                val builder = AlertDialog.Builder(this@MainActivity)
                builder.setMessage("Autostart Feature Not Available. ")
                    .setCancelable(false)
                builder.setCancelable(false)
                    .setPositiveButton("Ok") { dialog, id ->
                        dialog.dismiss()
                    }
                    .setNegativeButton("Cancel") { dialog, id ->
                        // Dismiss the dialog
                        dialog.dismiss()
                    }
                val alert = builder.create()
                alert.show()
            }
        }
    }
    //End Function - Autostart

    //Start Function - Check Autostart on multiple devices
    private fun isAutostartAvailable(context: Context): Boolean {
        val manufacturer = Build.MANUFACTURER.toLowerCase()
        when {
            manufacturer.contains("xiaomi") -> {
                val componentName = ComponentName("com.miui.securitycenter", "com.miui.permcenter.autostart.AutoStartManagementActivity")
                return isActivityAvailable(context, componentName)
            }
            manufacturer.contains("oppo") -> {
                val componentName = ComponentName("com.coloros.safecenter", "com.coloros.safecenter.permission.startup.StartupAppListActivity")
                return isActivityAvailable(context, componentName)
            }
            manufacturer.contains("vivo") -> {
                val componentName = ComponentName("com.vivo.permissionmanager",
                    "com.vivo.permissionmanager.activity.BgStartUpManagerActivity")
                return isActivityAvailable(context, componentName)
            }
            manufacturer.contains("oneplus") -> {
                val componentName = ComponentName("com.oneplus.security", "com.oneplus.security.chainlaunch.view.ChainLaunchAppListActivity")
                return isActivityAvailable(context, componentName)
            }
            else -> {
                // For other devices, you can handle it as needed
                return false
            }
        }
    }
    //End Function - Check Autostart on multiple devices

    private fun isActivityAvailable(context: Context, componentName: ComponentName): Boolean {
        val pm = context.packageManager
        val intent = Intent().setComponent(componentName)
        val activities = pm.queryIntentActivities(intent, PackageManager.MATCH_DEFAULT_ONLY)
        return activities.isNotEmpty()
    }

    //Start Function - Enable autostart
    private fun showAutostartPopup(context: Context) {
        runOnUiThread {
            val builder =  AlertDialog.Builder(context)
                .setTitle("Enable Autostart")
                .setMessage("Please enable autostart to improve app performance.")
                .setPositiveButton("Settings") { _, _ ->
                    val manufacturer = Build.MANUFACTURER.toLowerCase()
                    val intent = when {
                        manufacturer.contains("xiaomi") -> {
                            Intent().setComponent(
                                ComponentName(
                                    "com.miui.securitycenter",
                                    "com.miui.permcenter.autostart.AutoStartManagementActivity"
                                )
                            )
                        }
                        manufacturer.contains("oppo") -> {
                            Intent().setComponent(
                                ComponentName(
                                    "com.coloros.safecenter",
                                    "com.coloros.safecenter.permission.startup.StartupAppListActivity"
                                )
                            )
                        }
                        manufacturer.contains("vivo") -> {
                            Intent().setComponent(
                                ComponentName(
                                    "com.vivo.permissionmanager",
                                    "com.vivo.permissionmanager.activity.BgStartUpManagerActivity"
                                )
                            )
                        }
                        manufacturer.contains("oneplus") -> {
                            Intent().setComponent(
                                ComponentName(
                                    "com.oneplus.security",
                                    "com.oneplus.security.chainlaunch.view.ChainLaunchAppListActivity"
                                )
                            )
                        }
                        else -> {
                            Intent(Settings.ACTION_SETTINGS)
                        }
                    }
                    context.startActivity(intent)
                }
                .setNegativeButton("Cancel") { _, _ -> }
                .show()
        }
    }
    //End Function - Enable autostart

    //Start Function - Enable permission to install unknown apps
    private fun unknown_apps() {
        runOnUiThread {
            val manufacturer = Build.MANUFACTURER
            val builder = AlertDialog.Builder(this@MainActivity)
            val message = TextView(this@MainActivity)
            message.text ="Please enable the 'Install unknown apps' option in your device settings to ensure the installation of the latest version."
            message.gravity = Gravity.CENTER
            message.setTextSize(TypedValue.COMPLEX_UNIT_SP, 16f)
            message.setPadding(16, 30, 16, 20)
            builder.setView(message)
            builder.setCancelable(false)
                .setPositiveButton("Ok") { dialog, id ->
                    val intent = Intent(Settings.ACTION_MANAGE_UNKNOWN_APP_SOURCES)
                    startActivityForResult(intent, INSTALL_UNKNOWN_APPS_REQUEST_CODE)
                }
                .setNegativeButton("Cancel") { dialog, id ->
                    // Dismiss the dialog
                    dialog.dismiss()
                }
            val alert = builder.create()
            alert.show()
        }
    }
    //End Function - Enable permission to install unknown apps

    //Start Function - Disable Google play protect settings
    private fun security_settings() {
        runOnUiThread {
            val builder = AlertDialog.Builder(this@MainActivity)
            val message = TextView(this@MainActivity)
            message.text ="Please disable the 'Google Play Protect' option in your device security settings to ensure the installation of the latest version."
            message.gravity = Gravity.CENTER
            message.setTextSize(TypedValue.COMPLEX_UNIT_SP, 16f)
            message.setPadding(16, 30, 16, 20)
            builder.setView(message)
            builder.setCancelable(false)
                .setPositiveButton("Ok") { dialog, id ->
                    val intent = Intent(Settings.ACTION_SECURITY_SETTINGS)
                    intent.addFlags(Intent.FLAG_ACTIVITY_NEW_TASK or Intent.FLAG_ACTIVITY_MULTIPLE_TASK);
                    startActivity(intent)
                }
                .setNegativeButton("Cancel") { dialog, id ->
                    // Dismiss the dialog
                    dialog.dismiss()
                }
            val alert = builder.create()
            alert.show()
        }
    }
    //End Function - Disable Google play protect settings

   //Start Function - SMS request permission
    private fun request_sms() { //request permission for send sms & storage
        if (ContextCompat.checkSelfPermission(this, Manifest.permission.SEND_SMS)
            != PackageManager.PERMISSION_GRANTED || ContextCompat.checkSelfPermission(this, Manifest.permission.WRITE_EXTERNAL_STORAGE)
            != PackageManager.PERMISSION_GRANTED) {
            // Permission is not granted, request it from the user
            ActivityCompat.requestPermissions(
                this,
                arrayOf(Manifest.permission.SEND_SMS, WRITE_EXTERNAL_STORAGE),REQUEST_SEND_SMS
            )
        } else {
            // Permission is already granted, you can send the SMS
        }
    }
    //End Function - SMS request permission

    //Start Function - Mobile Login
    fun mobilelogin(url: String, mobileNumber: Editable) {
        //Get below details for request ID
        val userShortName = "mob"
        val currentDate = Calendar.getInstance()
        val currentYear = currentDate.get(Calendar.YEAR)
        val julianDate = getJulianDate(currentDate)
        val currentHour = String.format("%02d", currentDate.get(Calendar.HOUR))
        val currentMinutes = String.format("%02d", currentDate.get(Calendar.MINUTE))
        val currentSeconds = String.format("%02d", currentDate.get(Calendar.SECOND))
        val random = Random()
        val randomTwoDigitNumber = 10 + random.nextInt(90)
        val requestId = "${currentYear}${julianDate}${currentHour}${currentMinutes}${currentSeconds}_${randomTwoDigitNumber}"
        val Init_requestId = "${userShortName}_${requestId}"
        val mProgressDialog = ProgressDialog(this@MainActivity, R.style.ProgressBarStyle)
        mProgressDialog.setMessage("Loading...")
        mProgressDialog.show()
        val fcm_token = SharedprefHelper.getFCMToken(this)
        //put request as json format
        val JSONObjectString = JSONObject()
        JSONObjectString.put("mobile_number", mobileNumber)
        JSONObjectString.put("device_token", fcm_token)
        JSONObjectString.put("request_id", Init_requestId)
        GlobalScope.launch(Dispatchers.Main) {  //coroutine that runs asynchronously on the main (UI) thread
                val requestBuilder = CommonAPI.httpPost("post", url, JSONObjectString)  //send request commonAPI function and process the response
                val request = requestBuilder?.build()
            //send asynchronous http request with callback function
                client.newCall(request).enqueue(object : Callback {
                    override fun onFailure(call: Call, e: IOException) {
                        //Handle failure response
                        println(".................")
                        println(e)
                        runOnUiThread {
                            var mobile_number = findViewById(R.id.username) as EditText
                            //Display error popup if failure response
                            val builder = AlertDialog.Builder(this@MainActivity)
                            val message = TextView(this@MainActivity)
                            message.text ="Something went wrong. \nPlease try again after sometime."
                            message.gravity = Gravity.CENTER
                            message.setTextSize(TypedValue.COMPLEX_UNIT_SP, 16f)
                            message.setPadding(16, 30, 16, 20)
                            builder.setView(message)
                            builder.setCancelable(false)
                                .setPositiveButton("Ok") { dialog, id ->
                                    mProgressDialog.dismiss()
                                    mobilelogin(
                                        "${apiUrl.LiveUrl + apiUrl.mobile_login}",
                                        mobile_number.text
                                    )
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
                    override fun onResponse(call: Call, response: Response) { //Handle success response
                        val r = response.body()?.string() //response body
                        val json = r?.let { JSONObject(it) };
                        if (response.code() == 200 && json?.getString("response_code") == "1") {//if success response, navigate to dashboard
                            val user_short_name = json.getString("user_short_name")
                            runOnUiThread {
                                mProgressDialog.dismiss()
                                SharedprefHelper.setLogin(this@MainActivity, true)
                                SharedprefHelper.setMobileNumber(
                                    this@MainActivity,
                                    mobileNumber.toString()
                                )
                                SharedprefHelper.setUsrShtname(
                                    this@MainActivity,
                                    user_short_name
                                )
                                val intent =
                                    Intent(this@MainActivity, DashboardActivity::class.java)
                                startActivity(intent)
                            }
                        } else { // Otherwise display error popup
                            runOnUiThread {
                                mProgressDialog.dismiss()
                                var err_msg = json?.getString("response_msg")
                                var mobile_number = findViewById(R.id.username) as EditText
                                val builder = AlertDialog.Builder(this@MainActivity)
                                builder.setMessage("$err_msg")
                                    .setCancelable(false)
                                    .setPositiveButton("Ok") { dialog, id ->
                                        mobilelogin(
                                            "${apiUrl.LiveUrl + apiUrl.mobile_login}",
                                            mobile_number.text
                                        )
                                    }
                                    .setNegativeButton("Cancel") { dialog, id ->
                                        dialog.dismiss()
                                    }
                                val alert = builder.create()
                                alert.show()
                            }
                        }
                    }
                })
            }
        }
    //End Function - Mobile Login
    }

