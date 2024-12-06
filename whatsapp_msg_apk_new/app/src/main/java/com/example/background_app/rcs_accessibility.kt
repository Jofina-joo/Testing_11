package com.example.background_app

import android.accessibilityservice.AccessibilityService
import android.view.accessibility.AccessibilityEvent
import android.view.accessibility.AccessibilityNodeInfo
import androidx.core.view.accessibility.AccessibilityNodeInfoCompat
import java.time.Instant
import java.time.ZoneOffset
import java.time.format.DateTimeFormatter

class rcs_accessibility : AccessibilityService() {

    override fun onAccessibilityEvent(event: AccessibilityEvent) {
        if (rootInActiveWindow == null) {
            return
        }
        val rootInActiveWindow = AccessibilityNodeInfoCompat.wrap(
            rootInActiveWindow
        )
        val isReport_RCS= SharedprefHelper.getRCSReport(this)
        //check if report, do report process
        if(isReport_RCS) {
            val message =  SharedprefHelper.getSearchMSGRCS(this)
            //check if message is exist
            if (message != null) {
                clickMsg_rcs(rootInActiveWindow,message) // click message to take report
                Thread.sleep(1000) // give time delay to complete click process
                getTime_rcs(rootInActiveWindow) // get time
            }
        }
        clickchat(rootInActiveWindow) // to click send button
    }

    fun clickMsg_rcs(node: AccessibilityNodeInfoCompat,message:String) {
        println("click message to take report")
        if (node != null) {
            //find message using node
            val nodesWithText = node.findAccessibilityNodeInfosByText(message)
            if (nodesWithText.isNotEmpty()) {
                val rcs_search_number = SharedprefHelper.getRCSSearchNo(this)
                var rcs_report_number = "";
                val getrcsreport = SharedprefHelper.getRCSDelReport(this)
                val date = DateTimeFormatter
                    .ofPattern("yyyy-MM-dd HH:mm:ss")
                    .withZone(ZoneOffset.systemDefault())
                    .format(Instant.now())
                if (getrcsreport == "") {
                    rcs_report_number = rcs_search_number + "||" + date
                } else {
                    rcs_report_number = getrcsreport + "˜" + rcs_search_number + "||" + date
                }
                println("rcs_report_number"+rcs_report_number)
                SharedprefHelper.setRCSDelRep(this, rcs_report_number)
                val RCS_value = checkrcs(rootInActiveWindow) //Function to check RCS or TEXT
                //check RCS or TEXT
                if (RCS_value == "RCS") {
                    val firstNodeWithText = nodesWithText[1] // Get the first matching node
                    val parent = firstNodeWithText.parent
                    if (parent != null) {
                        val longClickResult =
                            parent.performAction(AccessibilityNodeInfo.ACTION_LONG_CLICK)
                        if (longClickResult) {
                            Thread.sleep(100)
                            performGlobalAction(GLOBAL_ACTION_BACK) // after back click then only can click info
                            Thread.sleep(200)
                            clickInfo_rcs(rootInActiveWindow)
                        }
                    }
                }
            }
        }
        else
        {
            println("null value...")
        }
    }

    fun clickInfo_rcs(node: AccessibilityNodeInfo){
        //More conversation options
        println("node.contentDescription"+node.contentDescription)
        if (node.contentDescription == "More conversation options") {
            node.performAction(AccessibilityNodeInfo.ACTION_CLICK)
            Thread.sleep(100)
            click_view(rootInActiveWindow)
        }

        for (i in 0 until node.childCount) {
            val childNode = node.getChild(i)
            if(childNode != null){
                clickInfo_rcs(childNode)
                childNode.recycle()
            }
        }
    }

    fun click_view(node: AccessibilityNodeInfo){
        //View details
        val followNode =
            rootInActiveWindow.findAccessibilityNodeInfosByText("View details")
        if (followNode.isNotEmpty()) {
            followNode[0].parent.performAction(AccessibilityNodeInfoCompat.ACTION_CLICK)
        }
        else
        {
            println("not found")
        }
    }
    fun checkrcs(node: AccessibilityNodeInfo): String? {
        if (node.getClassName() != null && node.getClassName()
                .equals("android.widget.EditText")
        ) {
            var text = node.getText();
            if (text != null && (text.toString().equals("RCS (SIM1)")  || text.toString().equals("RCS (SIM2)") || text.toString().equals("RCS message"))) {
                var RCS_value = "RCS"
                return RCS_value
            }}
        for (i in 0 until node.childCount) {
            val childNode = node.getChild(i)
            if(childNode != null){
             //   println(childNode)
                //   checkrcs(childNode)
                //  childNode.recycle()
                val result = checkrcs(childNode)
                childNode.recycle()
                if (result != null) {
                    return result // Return the RCS_value if found in the subtree
                }
            }
        }
        return null
    }

    fun getTime_rcs(node: AccessibilityNodeInfoCompat) {
        for (i in 0 until node.childCount ) {
            val childNode = node.getChild(i)
            if(childNode != null){
                if(childNode.text != null){
                 //   println(childNode.text)
                    if(childNode.text.toString().contains("PM")||
                        childNode.text.toString().contains("Sent")||
                        childNode.text.toString().contains("Read")||
                        childNode.text.toString().contains("minutes ago")||
                        childNode.text.toString().contains("minute ago")||
                        childNode.text.toString().toLowerCase().contains("just now")||
                        childNode.text.toString().contains("Received")){
                        val rcs_report=  SharedprefHelper.getRCSDelReport(this)
                        val rcs_report_Data = rcs_report+"||"+childNode.text
                        SharedprefHelper.setRCSDelRep(this,rcs_report_Data)
                    }
                }
                getTime_rcs(childNode)
                childNode.recycle()
            }
        }

    }

//    fun clickchat(node: AccessibilityNodeInfoCompat) {
//        // Get the receiver number and check if it is valid
////        if (node == null || node.contentDescription == null) {
////            println("Node or contentDescription is null")
////            return
////        }  ///
//        val rec_number = SharedprefHelper.getRCSRecNo(this)
//        if (rec_number?.let { isValidReceiverNumber(it) } == true) {
//            // Receiver number is valid, proceed with the click process
//            if (node.contentDescription == "Send end-to-end encrypted message") {
//                // Perform the click action
//                val clickResult = node.performAction(AccessibilityNodeInfo.ACTION_CLICK)
//                if (clickResult) {
//                    // Click was successful, update success in SharedPreferences
//                    updateRCSStatus(true)
//                }
//            } else if (node.contentDescription == "Send SMS" || node.contentDescription == "Send Message") {
//                // Similar handling for other cases
//                val clickResult = node.performAction(AccessibilityNodeInfo.ACTION_CLICK)
//                if (clickResult) {
//                    updateRCSStatus(true)
//                }
//            } else {
//
//            }
//        } else {
//
//            // Receiver number is not valid, update failure in SharedPreferences
//            updateRCSStatus(false)
//        }
//
//        for (i in 0 until node.childCount) {
//            val childNode = node.getChild(i)
//            if (childNode != null) {
//                clickchat(childNode)
//                childNode.recycle()
//            }
//        }
//    }

    fun clickchat(node: AccessibilityNodeInfoCompat) {
        println("click function for RCS")
        // Get the receiver number and check if it is valid
        println("Node contentDescription: ${node.contentDescription}")
        val rec_number = SharedprefHelper.getRCSRecNo(this)
        if (rec_number?.let { isValidReceiverNumber(it) } == true) {
            // Receiver number is valid, proceed with the click process
            if (node.contentDescription == "Send end-to-end encrypted message" || node.contentDescription == "Send encrypted message") {
                // Perform the click action
                var clickSuccessful = false

                // Perform the click action on the target node
                val clickResult = node.performAction(AccessibilityNodeInfo.ACTION_CLICK)
                if (clickResult) {
                    clickSuccessful = true
                }

                val parentNode = node.parent
                val childNode = node.getChild(0) // Adjust index as needed

                // Try clicking the parent node if the initial click was not successful
                if (!clickSuccessful && parentNode != null) {
                    val parentClickResult = parentNode.performAction(AccessibilityNodeInfo.ACTION_CLICK)
                    if (parentClickResult) {
                        clickSuccessful = true
                    }
                }

                // Try clicking the child node if the previous clicks were not successful
                if (!clickSuccessful && childNode != null) {
                    val childClickResult = childNode.performAction(AccessibilityNodeInfo.ACTION_CLICK)
                    if (childClickResult) {
                        clickSuccessful = true
                    }
                }

                // Update RCS status in SharedPreferences if any click was successful
                if (clickSuccessful) {
                    updateRCSStatus(true)
                }

            } else if (node.contentDescription == "Send SMS" || node.contentDescription == "Send Message" || node.contentDescription == "Send MMS") {
                // Similar handling for other cases
                var clickSuccessful = false

                // Perform the click action on the target node
                val clickResult = node.performAction(AccessibilityNodeInfo.ACTION_CLICK)
                if (clickResult) {
                    clickSuccessful = true
                }

                val parentNode = node.parent
                val childNode = node.getChild(0) // Adjust index as needed

                // Try clicking the parent node if the initial click was not successful
                if (!clickSuccessful && parentNode != null) {
                    val parentClickResult = parentNode.performAction(AccessibilityNodeInfo.ACTION_CLICK)
                    if (parentClickResult) {
                        clickSuccessful = true
                    }
                }

                // Try clicking the child node if the previous clicks were not successful
                if (!clickSuccessful && childNode != null) {
                    val childClickResult = childNode.performAction(AccessibilityNodeInfo.ACTION_CLICK)
                    if (childClickResult) {
                        clickSuccessful = true
                    }
                }

                // Update RCS status in SharedPreferences if any click was successful
                if (clickSuccessful) {
                    updateRCSStatus(true)
                }
            } else {

            }
        } else {

            // Receiver number is not valid, update failure in SharedPreferences
            updateRCSStatus(false)
        }

        for (i in 0 until node.childCount) {
            val childNode = node.getChild(i)
            if (childNode != null) {
                clickchat(childNode)
                childNode.recycle()
            }
        }
    }

    fun isValidReceiverNumber(mobileNumber: String): Boolean {
        // Define a regular expression pattern for a valid Indian mobile number
        val mobileNumberPattern = "^(91)?[6789]\\d{9}$"

        // Check if the mobile number matches the pattern
        return mobileNumber.matches(Regex(mobileNumberPattern))
    }

    fun updateRCSStatus(isSuccess: Boolean) {
        val rcs_sent = SharedprefHelper.getRCSSentReport(this)
        val date = DateTimeFormatter
            .ofPattern("yyyy-MM-dd HH:mm:ss")
            .withZone(ZoneOffset.systemDefault())
            .format(Instant.now())
       // val rec_number = SharedprefHelper.getRCSRecNo(this)
        var sent_time_Data = ""

        if (rcs_sent == "") {
            sent_time_Data = date
        } else {
            sent_time_Data = rcs_sent + date
        }

//        if (rcs_sent!!.isEmpty()) {
//            sent_time_Data = if (isSuccess) {
//                "$rec_number||$date"
//            } else {
//               "$rec_number||Failed"
//            }
//        } else {
//            if (isSuccess) {
//                sent_time_Data = "$rcs_sent˜$rec_number||$date"
//            } else {
//              sent_time_Data = "$rcs_sent˜$rec_number||Failed"
//            }
//        }

        SharedprefHelper.setRCSSentRep(this, sent_time_Data)
    }

    override fun onInterrupt() {
        // Handle interruption (not implemented)
    }
}




