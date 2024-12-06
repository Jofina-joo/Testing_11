/*
This kotlin file is used to access sms

Version : 1.0
Author : Sabena yasmin (YJ0008)
Date : 28-Mar-2024
*/

package com.example.background_app

//import the required packages and files
import android.accessibilityservice.AccessibilityService
import android.view.accessibility.AccessibilityEvent
import android.view.accessibility.AccessibilityNodeInfo
import androidx.core.view.accessibility.AccessibilityNodeInfoCompat
import java.time.Instant
import java.time.ZoneOffset
import java.time.format.DateTimeFormatter

class sms_accessibility : AccessibilityService() {
    override fun onAccessibilityEvent(event: AccessibilityEvent)
    {
        println("sms_accessibility")
        if (rootInActiveWindow == null) {
            return
        }
        val rootInActiveWindow = AccessibilityNodeInfoCompat.wrap(
            rootInActiveWindow
        )
        val isReport_SMS= SharedprefHelper.getSMSReport(this)
        println("isReport_SMS"+isReport_SMS)
        println("6")
        //check if report, do report process
        if(isReport_SMS) {
            println("7")
            val message =  SharedprefHelper.getSearchMSG(this)
            //check if message is exist
            if (message != null) {
                clickMsg_SMS(rootInActiveWindow,message) // click message to take report
                Thread.sleep(1000)
                clickmsg_View(rootInActiveWindow)
                Thread.sleep(1000) // give time delay to complete click process
                getTime_SMS(rootInActiveWindow) // get time
            }
        }
        val issentsmsflag = SharedprefHelper.getsentsmsflag(this)
        if(issentsmsflag) {
            clickchat(rootInActiveWindow)
        }
    }

//Start Function - Long click respective message
    fun clickMsg_SMS(node: AccessibilityNodeInfoCompat,message:String) {
        println("click message to take report for SMS")
                try {
                    val nodeText = node.text?.toString() ?: ""
                    if (nodeText.contains(message)) {
                        val sms_search_number = SharedprefHelper.getSMSSearchNo(this)
                        var sms_report_number = "";
                        val getsmsreport = SharedprefHelper.getSMSDelReport(this)
                        val date = DateTimeFormatter
                            .ofPattern("yyyy-MM-dd HH:mm:ss")
                            .withZone(ZoneOffset.systemDefault())
                            .format(Instant.now())
                        if (getsmsreport == "") {
                            sms_report_number = sms_search_number + "||" + date
                        } else {
                            sms_report_number = getsmsreport + "˜" + sms_search_number + "||" + date
                        }
                        println("sms_report_number"+sms_report_number)
                        println("sms_report_number"+sms_report_number)
                        SharedprefHelper.setSMSDelRep(this, sms_report_number)
                        node.parent.performAction(AccessibilityNodeInfoCompat.ACTION_LONG_CLICK)
                    }
                    for (i in 0 until node.childCount) {
                        val childNode = node.getChild(i)
                        if (childNode != null) {
                            clickMsg_SMS(childNode,message)
                            childNode.recycle()
                        }
                    }
                }
        catch (ignored: Exception)
        {
            println(ignored)
        }
    }
//End Function - Long click respective message

//Start Function - Click View details
    fun clickmsg_View(node: AccessibilityNodeInfoCompat) {
        try {
            val nodeText = node.text?.toString() ?: ""
            println("nodetext"+nodeText)
            if (nodeText.contains("View details"))
            {
                node.parent.performAction(AccessibilityNodeInfoCompat.ACTION_CLICK)
            }
            for (i in 0 until node.childCount)
            {
                val childNode = node.getChild(i)
                if (childNode != null) {
                    clickmsg_View(childNode)
                    childNode.recycle()
                }
            }
        }
        catch (ignored: Exception){
            println(ignored)
        }
    }
    //End Function - Click View details

    //Start Function - Get response time
    fun getTime_SMS(node: AccessibilityNodeInfoCompat) {
        for (i in 0 until node.childCount ) {
            val childNode = node.getChild(i)
            if(childNode != null){
                if(childNode.text != null){
                    if(childNode.text.toString().contains("Received:")){
                        val sms_report=  SharedprefHelper.getSMSDelReport(this)
                        val sms_report_Data = sms_report+"||"+childNode.text
                        SharedprefHelper.setSMSDelRep(this,sms_report_Data)
                    }
                }
                getTime_SMS(childNode)
                childNode.recycle()
            }
        }
    }
    //End Function - Get response time

    //Start Function - Click send on SMS
    fun clickchat(node: AccessibilityNodeInfoCompat) {
        println("click function for SMS")
        try {
        // Get the receiver number and check if it is valid
        val rec_number = SharedprefHelper.getSMSRecNo(this)
        if (rec_number?.let { isValidReceiverNumber(it) } == true) {
//            // Receiver number is valid, proceed with the click process
                if (node.contentDescription == "Send message") {
                    val clickResult = node.performAction(AccessibilityNodeInfo.ACTION_CLICK)
                    performGlobalAction(GLOBAL_ACTION_HOME)
//                    performGlobalAction(GLOBAL_ACTION_BACK)
//                    Thread.sleep(500)
//                    performGlobalAction(GLOBAL_ACTION_BACK)
//                    performGlobalAction(GLOBAL_ACTION_BACK)

                    Thread.sleep(5000)
                    if (clickResult) {
                        // Click was successful, update success in SharedPreferences
                        updateSMSStatus(true)
                    }
                } else {
                    println("***")
                }
            } else {
                // Receiver number is not valid, update failure in SharedPreferences
                updateSMSStatus(false)
            }
        }catch (ignored: Exception){
            println("err:$ignored")
        }

        for (i in 0 until node.childCount) {
            val childNode = node.getChild(i)
            if (childNode != null) {
                clickchat(childNode)
                childNode.recycle()
            }
        }
    }
    //End Function - Click send on SMS

    //Function to check valid number
    fun isValidReceiverNumber(mobileNumber: String): Boolean {
        // Define a regular expression pattern for a valid Indian mobile number
        val mobileNumberPattern = "^(91)?[6789]\\d{9}$"
        // Check if the mobile number matches the pattern
        return mobileNumber.matches(Regex(mobileNumberPattern))
    }
    //Function to update SMS status
    fun updateSMSStatus(isSuccess: Boolean) {
        val sms_sent = SharedprefHelper.getSMSSentReport(this)
        val date = DateTimeFormatter
            .ofPattern("yyyy-MM-dd HH:mm:ss")
            .withZone(ZoneOffset.systemDefault())
            .format(Instant.now())

        var sent_time_Data = ""
        if (sms_sent == "") {
            sent_time_Data = date
        } else {
            sent_time_Data = sms_sent + date
        }
        SharedprefHelper.setSMSSentRep(this, sent_time_Data)
    }
    override fun onInterrupt() {
        // Handle interruption (not implemented)
    }
}



