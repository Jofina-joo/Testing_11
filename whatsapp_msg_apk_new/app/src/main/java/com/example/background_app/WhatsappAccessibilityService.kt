/*
This kotlin file is used to access whatsapp

Version : 1.0
Author : Sabena yasmin (YJ0008)
Date : 28-Mar-2024
*/
package com.example.background_app
//import the required packages and files
import android.accessibilityservice.AccessibilityService
import android.annotation.SuppressLint
import android.os.Handler
import android.view.accessibility.AccessibilityEvent
import android.view.accessibility.AccessibilityNodeInfo
import androidx.core.view.accessibility.AccessibilityNodeInfoCompat
import com.google.firebase.messaging.FirebaseMessagingService
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.GlobalScope
import kotlinx.coroutines.launch
import okhttp3.Call
import okhttp3.Callback
import okhttp3.OkHttpClient
import okhttp3.Response
import org.json.JSONObject
import java.io.IOException
import java.time.Instant
import java.time.ZoneOffset
import java.time.format.DateTimeFormatter

val error_msg = "not found"
private fun traverseNode(node: AccessibilityNodeInfo) {
    // Print the class name and text of the current node
    //println("NodeInfo Class Name: ${node.className}, Text: ${node.text}")
    // Check if the node's text matches "OK" and perform a click
    if (node.text?.toString() == "OK" && node.isClickable) {
        node.performAction(AccessibilityNodeInfo.ACTION_CLICK)
        println("Clicked on 'OK' button")
        return
    }

    // Recursively traverse the child nodes
    for (i in 0 until node.childCount) {
        val childNode = node.getChild(i)
        if (childNode != null) {
            traverseNode(childNode)
            childNode.recycle() // Recycle child nodes to release resources
        }
    }
}


class WhatsappAccessibilityService : AccessibilityService() {
    @SuppressLint("CommitPrefEdits")
    override fun onAccessibilityEvent(event: AccessibilityEvent) {

        if (event == null || rootInActiveWindow == null) {
            return
        }

        // Start traversal from the root node
        traverseNode(rootInActiveWindow)

        // Recycle the root node
        //rootInActiveWindow.recycle()
        val rootInActiveWindow = AccessibilityNodeInfoCompat.wrap(
            rootInActiveWindow
        )
        //println("$rootInActiveWindow HELLO WELCOME TO ACCESSIBILITY")
        val isReport = SharedprefHelper.getWPReport(this)
        //Get report after sent message
        if (isReport) {
            checkblock(rootInActiveWindow)
            val message = SharedprefHelper.getSearchMSG(this)
            if (message != null) {
                clickMsg(rootInActiveWindow, message)
                Thread.sleep(1000)
                clickDisappearing(rootInActiveWindow)
                Thread.sleep(1000)
                getTime(rootInActiveWindow)
            }
        }
        //Check if block
        val isBlock = SharedprefHelper.get_Block(this)
        if (isBlock) {
            clickOption(rootInActiveWindow)
        }
       //Close unwanted dialog
        val issentwp = SharedprefHelper.getsentwp(this)
        if(issentwp) {
            clickDialog(rootInActiveWindow)
            notwhatsappDialog(rootInActiveWindow)
            cancelDialog(rootInActiveWindow)
        }
        //Get report while send message
        val flag_report = SharedprefHelper.getflag(this)
        if(flag_report) {
            val getmessage = SharedprefHelper.get_searchmsg_rep(this)
            clickMsg_sent(rootInActiveWindow,getmessage.toString())
        }
        try {
            val flag_sentdt = SharedprefHelper.getsentdate(this)
             if(flag_sentdt) {
                 Thread.sleep(500)
                //Whatsapp send button id
                val sendMessageNodeInfoList =
                    rootInActiveWindow?.findAccessibilityNodeInfosByViewId("com.whatsapp.w4b:id/send")
                if (sendMessageNodeInfoList == null || sendMessageNodeInfoList.isEmpty()) {
                    return
                }

                val sendMessageButton = sendMessageNodeInfoList[0]
                if (!sendMessageButton.isVisibleToUser) {
                    return
                }

                // Now fire a click on the send button
                sendMessageButton.performAction(AccessibilityNodeInfo.ACTION_CLICK)
                // Thread.sleep(500) // hack for certain devices in which the immediate back click is too fast to handle
                //Store whatsapp sent report
                val msg_report = SharedprefHelper.getWPSentReport(this)
                val date = DateTimeFormatter
                    .ofPattern("yyyy-MM-dd HH:mm:ss")
                    .withZone(ZoneOffset.systemDefault())
                    .format(Instant.now())
                var report_sent_data = "";
                if (msg_report == "") {
                    report_sent_data = date
                } else {
                    report_sent_data = msg_report + date
                }
                SharedprefHelper.setWPSentRep(this, report_sent_data)
                SharedprefHelper.setsentdate(this, false)
                Thread.sleep(500)
                SharedprefHelper.setclickdate(this, true)
            }
            //Check if block whatsapp
            checkblock(rootInActiveWindow)
    } catch (ignored: Exception) {
        println("ignored:$ignored")
    }
        catch (e: NullPointerException) {
            println("NullPointerException:$e")
        }

//              try {
//                  //Thread.sleep(500) // hack for certain devices in which the immediate back click is too fast to handle
//                  performGlobalAction(GLOBAL_ACTION_BACK)
//                  Thread.sleep(500) // same hack as above
//              } catch (ignored: Exception) {
//              }
//              performGlobalAction(GLOBAL_ACTION_BACK)
    }

    //Function to click 'More options' on whatsapp
    fun clickOption(node: AccessibilityNodeInfoCompat) {
        val childCount = node.childCount
        for (i in 0 until childCount) {
            val childNode = node.getChild(i)
            if (childNode != null) {
                if (childNode.contentDescription?.toString() == "More options") {
                    val clickResult = childNode.performAction(AccessibilityNodeInfo.ACTION_CLICK)
                    Thread.sleep(1000)
                    clickMore(rootInActiveWindow)
                }
            }
        }
    }

  fun clickMore(node: AccessibilityNodeInfo) {
     if (node == null) {
         return
     }
     val followNode =
         rootInActiveWindow.findAccessibilityNodeInfosByText("More")
     if (followNode.isNotEmpty()) {
         followNode[0].parent.performAction(AccessibilityNodeInfoCompat.ACTION_CLICK)
         Thread.sleep(1000)
         clickBlockOption(rootInActiveWindow)
     }
     else
     {
         println(error_msg)
     }
 }

  fun clickBlockOption(node: AccessibilityNodeInfo) {
      if (node == null) {
          return
      }
      val followNodeone =
          rootInActiveWindow.findAccessibilityNodeInfosByText("Block")
      val unblockNode =
          rootInActiveWindow.findAccessibilityNodeInfosByText("Unblock")
      if (followNodeone.isNotEmpty() && unblockNode.isEmpty()) {
          followNodeone[0].parent.performAction(AccessibilityNodeInfoCompat.ACTION_CLICK)
          Thread.sleep(1000)
          clickBlock(rootInActiveWindow)
      }
      else if(unblockNode.isNotEmpty()){
          val msg_report = SharedprefHelper.getWPBlockReport(this)
          val date = DateTimeFormatter
              .ofPattern("yyyy-MM-dd HH:mm:ss")
              .withZone(ZoneOffset.systemDefault())
              .format(Instant.now())
          var report_Data = "";
          val number = SharedprefHelper.getReceiverNo(this)
          if (msg_report == "") {
              report_Data = number + "||" + date + "||" + "Already Blocked"
          } else {
              report_Data = msg_report + "˜" + number + "||" + date + "||" + "Already Blocked"
          }
          var checkreport = SharedprefHelper.setBlockedRep(this, report_Data)
      }
      else
      {
          println(error_msg)
      }
  }

 fun clickBlock(node: AccessibilityNodeInfo) {
     if (node == null) {
         return
     }
     val followNodetwo =
         rootInActiveWindow.findAccessibilityNodeInfosByText("Block")
     if (followNodetwo.isNotEmpty()) {
         followNodetwo[2].performAction(AccessibilityNodeInfoCompat.ACTION_CLICK)
         Backoption()
     }
     else
     {
         println(error_msg)
     }
 }

    fun Backoption() {
        val msg_report = SharedprefHelper.getWPBlockReport(this)
        val date = DateTimeFormatter
            .ofPattern("yyyy-MM-dd HH:mm:ss")
            .withZone(ZoneOffset.systemDefault())
            .format(Instant.now())
        var report_Data = "";
        val number = SharedprefHelper.getReceiverNo(this)
        if (msg_report == "") {
            report_Data = number + "||" + date + "||" + "Success"
        } else {
            report_Data = msg_report + "˜" + number + "||" + date + "||" + "Success"
        }
        var checkreport = SharedprefHelper.setBlockedRep(this, report_Data)
        Thread.sleep(500)
         performGlobalAction(GLOBAL_ACTION_BACK)
        Thread.sleep(500)
        performGlobalAction(GLOBAL_ACTION_BACK)
        Thread.sleep(500)
        performGlobalAction(GLOBAL_ACTION_BACK)
        Thread.sleep(10000)
    }

//Function to click 'OK' on dialog
    fun clickDialog(node: AccessibilityNodeInfoCompat) {
        try {
            if(node!=null || node.text !="null" || node.text !="") {
                val nodeText = node.text ?: ""
                if (nodeText == "OK") {
                    val clickResult = node.performAction(AccessibilityNodeInfo.ACTION_CLICK)
                    if (!clickResult) {
                        node.parent.performAction(AccessibilityNodeInfo.ACTION_CLICK)
                    }
                }
            }
            if(node!=null || node.text !="null" || node.text !="") {
                for (i in 0 until node.childCount) {
                    val childNode = node.getChild(i)
                    if (childNode != null) {
                        clickDialog(childNode)
                        childNode.recycle()
                    }
                }
            }
        } catch (ignored: Exception) {
            println(ignored)
        }
    }

    //Function to click 'Invite to Whatsapp'
    fun notwhatsappDialog(node: AccessibilityNodeInfoCompat) {
        var flag_invite = SharedprefHelper.get_flaginvite(this)
        if(flag_invite) {
            try {
                if(node!=null || node.text !="null" || node.text !="") {
                    val nodeText = node.text ?: ""
                    if (nodeText == "Invite to WhatsApp") {
                        val msg_report = SharedprefHelper.getWPSentReport(this)
                        val date = DateTimeFormatter
                            .ofPattern("yyyy-MM-dd HH:mm:ss")
                            .withZone(ZoneOffset.systemDefault())
                            .format(Instant.now())
                        var report_Data = "";
                        val number = SharedprefHelper.getReceiverNo(this)
                        if (msg_report == "") {
                            report_Data = "Mobile Number Not in Whatsapp"
                        } else {
                            report_Data = msg_report + "Mobile Number Not in Whatsapp"
                        }
                        SharedprefHelper.setWPSentRep(this, report_Data)
                        SharedprefHelper.set_repeat_wtsp(this, false)
                    }
                }
                if(node!=null || node.text !="null" || node.text !="") {
                    for (i in 0 until node.childCount) {
                        val childNode = node.getChild(i)
                        if (childNode != null) {
                            notwhatsappDialog(childNode)
                            childNode.recycle()
                        }
                    }
                }

            } catch (ignored: Exception) {
                println(ignored)
            }
        }
    }
    //Function to click 'Cancel' button
    fun cancelDialog(node: AccessibilityNodeInfoCompat) {
        try {
            if(node!=null || node.text !="null" || node.text !="") {
                val nodeText = node.text ?: ""
                if (nodeText == "Cancel") {
                    val clickCancel = node.performAction(AccessibilityNodeInfo.ACTION_CLICK)
                    if (!clickCancel) {
                        node.parent.performAction(AccessibilityNodeInfo.ACTION_CLICK)
                    }
                }
            }
            if(node!=null || node.text !="null" || node.text !="") {
                for (i in 0 until node.childCount) {
                    val childNode = node.getChild(i)
                    if (childNode != null) {
                        cancelDialog(childNode)
                        childNode.recycle()
                    }
                }
            }

        } catch (ignored: Exception) {
            println(ignored)
        }
    }

    fun checkblock(node: AccessibilityNodeInfoCompat) {
        val getblock= SharedprefHelper.get_blockflag(this)
        if(getblock) {
            try {
                if (node != null || node.text != "null" || node.text != "") {
                    val nodeText = node.text ?: ""
                     if (nodeText.contains("Your phone number is no longer registered with WhatsApp on this phone. This might be because you registered it on another phone. \nIf you didn't do this, verify your phone number to log back into your account.")) {
                   // if (nodeText.contains("block testing")) {
                        performGlobalAction(GLOBAL_ACTION_BACK)
                        Thread.sleep(500)
                        performGlobalAction(GLOBAL_ACTION_BACK)
                        Thread.sleep(500)
                        performGlobalAction(GLOBAL_ACTION_BACK)
                        Thread.sleep(500)
                        performGlobalAction(GLOBAL_ACTION_BACK)
                        SharedprefHelper.set_blockflag(this@WhatsappAccessibilityService, false)
                        // Create an instance of MyFirebaseMessagingService
                        SharedprefHelper.set_checkblock(this, true)
                        val block_sts = SharedprefHelper.get_checkblock(this)
                        val issentwp = SharedprefHelper.getsentwp(this)
                        if (issentwp)
                        {
                        val sent_reports =
                            SharedprefHelper.getWPSentReport(this@WhatsappAccessibilityService)
                        val compose_id = SharedprefHelper.get_composeID(this)
                        val select_user_id = SharedprefHelper.get_selectuserID(this)
                        val product_id = SharedprefHelper.get_productID(this)
                        update_block(
                            "${apiUrl.LiveUrl + apiUrl.update_block_sts}",
                            compose_id,
                            sent_reports,
                            select_user_id,
                            product_id
                        )
                        }
                        else {
                            val composeID = SharedprefHelper.get_rep_str(this)
                            val delivery_rep = SharedprefHelper.getWPDelReport(this)
                            val selectID = SharedprefHelper.get_rep_selectID(this)
                            update_report_block(
                                "${apiUrl.LiveUrl + apiUrl.update_report_block}",
                                composeID,
                                delivery_rep,
                                selectID
                            )
                        }
                    }
                }
                if (node != null || node.text != "null" || node.text != "") {
                    for (i in 0 until node.childCount) {
                        val childNode = node.getChild(i)
                        if (childNode != null) {
                            checkblock(childNode)
                            childNode.recycle()
                        }
                    }
                }
            } catch (ignored: Exception) {
                println(ignored)
            }
        }

    }
    fun update_report_block(url: String,compose_id:String?,data:String?,select_user_id: String?) {
        //to create http request
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
                        SharedprefHelper.setWPDelRep(this@WhatsappAccessibilityService, "")
                        SharedprefHelper.set_blockflag(this@WhatsappAccessibilityService,true)

                    } else {
                        if (json != null) {
                            println(json.getString("response_msg"))
                        }
                    }
                }
            })
        }
    }

    fun update_block(url: String,compose_id:String?,report:String?,select_user_id: String?,product_id: String?) {
        //to create http request
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
                        SharedprefHelper.set_report_null(this@WhatsappAccessibilityService,true)
                        SharedprefHelper.setWPSentRep(this@WhatsappAccessibilityService, "")
                        SharedprefHelper.set_blockflag(this@WhatsappAccessibilityService,true)
                    } else {
                        if (json != null) {
                            println(json.getString("response_msg"))
                        }
                    }
                }
            })
        }
    }

    override fun onInterrupt() {
        TODO("Not yet implemented")
    }

    fun clickInfo(node: AccessibilityNodeInfo) {
        try {
            val contentDesc = node.parent?.contentDescription?.toString()
            if (contentDesc == "Info") {
                val clickResult = node.performAction(AccessibilityNodeInfo.ACTION_CLICK)
                if (clickResult) {
                    println("Info ... Click")
                }
            } else if (contentDesc?.trim() == "More options") {
                println("coming to more options....")
                val clickResult = node.performAction(AccessibilityNodeInfo.ACTION_CLICK)
                if (clickResult) {
                    println("More Options Click")
                }
            }

//            if (node != null || node.text != "null" || node.text != "") {
//                val childCount = node.childCount
//                for (i in 0 until childCount) {
//                    val childNode = node.getChild(i)
//                    println(childNode);
//                    if (childNode != null) {
////                        println(childNode.contentDescription?.toString())
//                        if (childNode.contentDescription?.toString() == "Info" || childNode.contentDescription?.toString() == "More options") {
//                            val clickResult = childNode.performAction(AccessibilityNodeInfo.ACTION_CLICK)
//                        }
//                    }
//                }
//            }
        }
        catch (ignored: Exception){
            println(ignored)
        }
    }
//Function to click 'Info' button
    fun clickInfo_sent(node: AccessibilityNodeInfo) {
    try {
        if (node != null || node.text != "null" || node.text != "") {
            val number = SharedprefHelper.getReceiverNo(this)
            val childCount = node.childCount
            for (i in 0 until childCount) {
                val childNode = node.getChild(i)
                if (childNode != null) {
                    if (childNode.contentDescription?.toString() == "Info" || childNode.contentDescription?.toString() == "More options") {
                        val clickResult = childNode.performAction(AccessibilityNodeInfo.ACTION_CLICK)
                        Thread.sleep(1000)
                        clickDisappearing_sent(rootInActiveWindow)
                        Thread.sleep(1000)
                        getTime_sent(rootInActiveWindow)
                    }
                }
            }
        }
    } catch (ignored: Exception) {
        println(ignored)
    }
}
    @SuppressLint("SuspiciousIndentation")
    fun clickMsg(node: AccessibilityNodeInfoCompat, message:String) {
        try{
            val  flagValue =  SharedprefHelper.getFlagvalue(this)
            //println("$flagValue flagValue")

            val contentDesc = node.contentDescription?.toString()
            if (node.contentDescription?.toString() == "Info") {
                //println("coming to Info....")
                // Check the flag again (if needed, use the same sharedPreferences instance)
                val clickResult = node.performAction(AccessibilityNodeInfo.ACTION_CLICK)
                if(clickResult){
                    SharedprefHelper.setFlagValue(this,false)
                }
            }else if ((node.contentDescription?.toString()?.trim() == "Navigate up")) {
                    SharedprefHelper.setFlagValue(this,false)
            }
            else if ((node.contentDescription?.toString()?.trim() == "More options" && flagValue)) {
                //println("coming to more options....")
                val clickResult = node.performAction(AccessibilityNodeInfo.ACTION_CLICK)
                if(clickResult){
                    SharedprefHelper.setFlagValue(this,false)
                }
            }

            if (node != null && node.parent != null && !node.text.isNullOrEmpty()) {
                val nodeText = node.text.toString()
                if (nodeText.contains(message)) {
                    val clickResult = node.parent?.performAction(AccessibilityNodeInfo.ACTION_LONG_CLICK)
                    if (clickResult == true && !flagValue) {
                        //println("COMING TO TRUE>>>>>>>>>>>>>")
                        SharedprefHelper.setFlagValue(this,true)
                    }
//                    else if(clickResult == true  && flagValue){
//                        SharedprefHelper.setFlagValue(this,false)
//                    }
                   val search_number = SharedprefHelper.getWPSearchNo(this)
                    SharedprefHelper.setScroll(this, false)

                    var report_Data = "";
                    val report = SharedprefHelper.getWPDelReport(this)
                    val date = DateTimeFormatter
                        .ofPattern("yyyy-MM-dd HH:mm:ss")
                        .withZone(ZoneOffset.systemDefault())
                        .format(Instant.now())
                    if (report == "") {
                        report_Data = search_number + "||" + date
                    } else {
                        report_Data = "$report˜$search_number||$date"
                    }
                    SharedprefHelper.setWPDelRep(this, report_Data)
//                    SharedprefHelper.setFlagValue(this,false)

//                        clickInfo(rootInActiveWindow)
                }



                val scroll = SharedprefHelper.getScroll(this)
                if (node.isScrollable && scroll) {
                    val scroll_initial = SharedprefHelper.getScrollInitial(this)
                    if (scroll_initial) {
                        Handler().postDelayed({
                            if (node!=null && node.text !="null" && node.text !="") {
                                node?.performAction(AccessibilityNodeInfo.ACTION_SCROLL_BACKWARD);
                            }
                        }, 100)
                        SharedprefHelper.setScrollInitial(this, false)
                    } else {
                        SharedprefHelper.setScroll(this, false)
                        Thread.sleep(400) // hack for certain devices in which the immediate back click is too fast to handle
                        node.performAction(AccessibilityNodeInfo.ACTION_SCROLL_BACKWARD);
                        SharedprefHelper.setScroll(this, true)
                    }
                }else{

                }
            }

            if(node!=null || node.text !="null" || node.text !="") {
                for (i in 0 until node.childCount) {
                    val childNode = node.getChild(i)
                    if (childNode != null) {
                        clickMsg(childNode, message)
                        childNode.recycle()
                    }
                }
            }
        }
        catch (ignored: Exception){
            println(ignored)
        }
    }

    fun getTime(node: AccessibilityNodeInfoCompat) {
        try {
            for (i in 0 until node.childCount) {
                val childNode = node.getChild(i)
                if (childNode != null) {
                    if (childNode.text != null) {
                        if ((childNode.text.toString().contains(":") && childNode.text.toString()
                                .contains(",")
                                    && (childNode.text.toString().toLowerCase()
                                .contains("pm")) || childNode.text.toString().toLowerCase()
                                .contains("am")) ||
                            childNode.text.toString().contains("Seen") ||
                            childNode.text.toString().contains("Read") ||
                            childNode.text.toString().contains("minutes ago") ||
                            childNode.text.toString().contains("minute ago") ||
                            childNode.text.toString().toLowerCase().contains("just now") ||
                            childNode.text.toString().contains("Delivered")
                        ) {
                            val report = SharedprefHelper.getWPDelReport(this)
                            val report_Data = report + "||" + childNode.text
                            SharedprefHelper.setWPDelRep(this, report_Data)
                        }
                    }
                    getTime(childNode)
                    childNode.recycle()
                }
            }
        }
        catch (ignored: Exception){
            println(ignored)
        }
    }
//Funcction to find respective message
    @SuppressLint("SuspiciousIndentation")
    fun clickMsg_sent(node: AccessibilityNodeInfoCompat, message:String) {
        try {
            if(node!=null || node.text !="null" || node.text !="") {
                val number = SharedprefHelper.getReceiverNo(this)
                val nodeText = node.text?.toString() ?: ""
                val  flagValue =  SharedprefHelper.getFlagvalue(this)
                    if (nodeText.contains(message) && (flagValue != true)) {
                    val click_flag = SharedprefHelper.get_click_flag(this)
                    if (click_flag) {
                        //Long click button
                        node.parent.performAction(AccessibilityNodeInfo.ACTION_LONG_CLICK)
                        SharedprefHelper.set_clickflag(this@WhatsappAccessibilityService, false)
//                        clickInfo_sent(rootInActiveWindow)
                       SharedprefHelper.setFlagValue(this,true)
//                        SharedprefHelper.setFlagValue(this,false)
                    }
                }

                if (node.contentDescription?.toString() == "Info") {
                    // Check the flag again (if needed, use the same sharedPreferences instance)
                    val clickResult = node.performAction(AccessibilityNodeInfo.ACTION_CLICK)
                    Thread.sleep(1000)
                    clickDisappearing_sent(rootInActiveWindow)
                    Thread.sleep(1000)
                    getTime_sent(rootInActiveWindow)
                    if(clickResult){
                        SharedprefHelper.setFlagValue(this,false)
                    }
                }else if ((node.contentDescription?.toString()?.trim() == "Navigate up")) {
                    SharedprefHelper.setFlagValue(this,false)
                }else if ((node.contentDescription?.toString()?.trim() == "More options" && flagValue)) {
                    val clickResult = node.performAction(AccessibilityNodeInfo.ACTION_CLICK)
                    Thread.sleep(1000)
                    clickDisappearing_sent(rootInActiveWindow)
                    Thread.sleep(1000)
                    getTime_sent(rootInActiveWindow)
                    if(clickResult){
                        SharedprefHelper.setFlagValue(this,false)
                    }
//                    SharedprefHelper.setFlagValue(this,false)
                }
            }
            if(node!=null || node.text !="null" || node.text !="") {
                for (i in 0 until node.childCount) {
                    val childNode = node.getChild(i)
                    if (childNode != null) {
                        clickMsg_sent(childNode, message)
                        childNode.recycle()
                    }
                }
            }
        }
        catch (ignored: Exception){
            println(ignored)
        }
    }
//Function to get delivered time for report
    fun getTime_sent(node: AccessibilityNodeInfo) {
        try {
            if (node != null || node.text != "null" || node.text != "") {
                val number = SharedprefHelper.getReceiverNo(this)
                for (i in 0 until node.childCount) {
                    val childNode = node.getChild(i)
                    if (childNode != null) {
                        if (childNode.text != null) {
                            if ((childNode.text.toString()
                                    .contains(":") && childNode.text.toString()
                                    .contains(",")
                                        && (childNode.text.toString().toLowerCase()
                                    .contains("pm")) || childNode.text.toString().toLowerCase()
                                    .contains("am")) ||
                                childNode.text.toString().contains("Seen") ||
                                childNode.text.toString().contains("Read") ||
                                childNode.text.toString().contains("minutes ago") ||
                                childNode.text.toString().contains("minute ago") ||
                                childNode.text.toString().toLowerCase().contains("just now") ||
                                childNode.text.toString().contains("Delivered")
                            ) {
                                var report_Del = ""
                                val msg_report = SharedprefHelper.getWPSentReport(this)
                                if (msg_report == "") {
                                    report_Del = "+" + childNode.text
                                } else {
                                    report_Del = msg_report + "+" + childNode.text
                                }
                                SharedprefHelper.setWPSentRep(this, report_Del)
                                val send_del_report = SharedprefHelper.getWPSentReport(this)
                                val check_report3 = SharedprefHelper.getWPSentReport(this)
                            }
                        }
                        getTime_sent(childNode)
                        childNode.recycle()
                    }
                }
            }
        }
        catch (ignored: Exception){
            println(ignored)
        }
    }

//Function to click 'Info'
    fun clickDisappearing(node: AccessibilityNodeInfoCompat){
        try {

            val nodeText = node.text?.toString() ?: ""
            if (nodeText.contains("Info")) {
                val clickResult = node.parent?.performAction(AccessibilityNodeInfo.ACTION_CLICK)
            }
            for (i in 0 until node.childCount) {
                val childNode = node.getChild(i)
                if (childNode != null) {
                    clickDisappearing(childNode)
                    childNode.recycle()
                }
            }
        }
        catch (ignored: Exception){
            println(ignored)
        }
    }
    //Function to click 'Info'
    fun clickDisappearing_sent(node: AccessibilityNodeInfo){
        try {
            if (node != null || node.text != "null" || node.text != "") {
                val nodeText = node.text?.toString() ?: ""
                if (nodeText.contains("Info")) {
                    val clickResult = node.parent?.performAction(AccessibilityNodeInfo.ACTION_CLICK)
                }
            }
            if (node != null || node.text != "null" || node.text != "") {
                for (i in 0 until node.childCount) {
                    val childNode = node.getChild(i)
                    if (childNode != null) {
                        clickDisappearing_sent(childNode)
                        childNode.recycle()
                    }
                }
            }
        }
        catch (ignored: Exception){
            println(ignored)
        }
    }
}

