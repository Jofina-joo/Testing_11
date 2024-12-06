/*
This kotlin file is used to update app version automatically

Version : 1.0
Author : Sabena yasmin (YJ0008)
Date : 28-Mar-2024
*/

package com.example.background_app

//import the required packages and files
import android.accessibilityservice.AccessibilityService
import android.annotation.SuppressLint
import android.view.accessibility.AccessibilityEvent
import android.view.accessibility.AccessibilityNodeInfo
import androidx.core.view.accessibility.AccessibilityNodeInfoCompat

class MessageAppAccessibilityService : AccessibilityService() {
    var is_appUpdated = false
    @SuppressLint("SuspiciousIndentation")
    override fun onAccessibilityEvent(event: AccessibilityEvent?) {
        if (rootInActiveWindow == null) {
            return
        }
        val rootInActiveWindow =  AccessibilityNodeInfoCompat.wrap(
            rootInActiveWindow
        )
         is_appUpdated= SharedprefHelper.getAppupdated(this)
        if(is_appUpdated == true) {
            // Perform the search for nodes with text "UPDATE" when the window content changes
            clickUpdateNodes(rootInActiveWindow)
            Thread.sleep(2000)
            clickOpenNodes(rootInActiveWindow)
        }
         }
    //Start Function - Click Open Button after update app
    private fun clickOpenNodes(node: AccessibilityNodeInfoCompat) {
        val openNodes = node.findAccessibilityNodeInfosByText("OPEN")
        val openNodes2 = node.findAccessibilityNodeInfosByText("Open Settings App info application info.")
        val openNodes3 = node.findAccessibilityNodeInfosByText("Open Message app application info.")
        val openNodes4 = node.findAccessibilityNodeInfosByText("Open Settings application info.")
        val openNodes5 = node.findAccessibilityNodeInfosByText("Open Downloads application info.")
        val openNodes6 = node.findAccessibilityNodeInfosByText("Open App drawer")
        if (openNodes.isNotEmpty() && openNodes2.isEmpty() && openNodes3.isEmpty() && openNodes4.isEmpty() && openNodes5.isEmpty() && openNodes6.isEmpty() ) {
            // Click on each found node
            for (node in openNodes) {
                if (node.text != null && node.text.toString().trim().equals("OPEN", ignoreCase = true)) {
                    node.performAction(AccessibilityNodeInfo.ACTION_CLICK)
                }
            }
        }
    }
    //End Function - Click Open Button after update app

   //Start Function - Click Update Button after update app
    private fun clickUpdateNodes(node: AccessibilityNodeInfoCompat) {
        val updateNodes = node.findAccessibilityNodeInfosByText("UPDATE")
        if (updateNodes.isNotEmpty()) {
            // Click on each found node
            for (node in updateNodes) {
                node.performAction(AccessibilityNodeInfo.ACTION_CLICK)
            }
        }
    }
    //End Function - Click Update Button after update app

    override fun onInterrupt() {
        // Implement this method if your service is interrupted
    }
}
