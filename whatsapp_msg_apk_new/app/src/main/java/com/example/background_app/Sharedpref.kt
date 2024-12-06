/*
This kotlin file is used to set and get sharedpreference

Version : 1.0
Author : Sabena yasmin (YJ0008)
Date : 28-Mar-2024
*/

package com.example.background_app

import android.content.Context

class Sharedpref {

    //Sharedpreference key values
    var _Mlogin = "isLogin"
    var _CHECKFlag = "flag_value"
    var _MFCMtoken = "fcm_token"
    var _MMobileNumber = "mobile_number"
    var _MReportSMS = "report_sms"
    var _MReportRCS = "report_rcs"
    var _MReportWP = "report"
    var _MSearchMSG = "search_message"
    var _MRCSSearchMSG = "search_message_rcs"
    var _MSMSSentReport = "sms_sent_rep"
    var _MRCSSentReport = "rcs_sent_rep"
    var _MReceiverNo = "receiver_number"
    var _MSMSReceiverNo = "SMS_rec_number"
    var _MRCSReceiverNo = "RCS_rec_number"
    var _MWPSentReport = "msg_report"
    var _MWPDelReport = "report_data"
    var _MWPSearchNo = "search_number"
    var _MUsershtname = "user_short_name"
    var _MSMSDelReport = "sms_report_data"
    var _MRCSDelReport = "rcs_report_data"
    var _MSMSSearchNo = "sms_search_number"
    var _MRCSSearchNo = "rcs_search_number"
    var _MSetBack = "back"
    var _MSetBack_rep = "back_report"
    var MWtsp_media = "wtsp_download_media"
    var MRCS_media = "rcs_download_media"
    var MRCS_filepath = "rcs_filepath"
    var MWtsp_filepath = "wtsp_filepath"
    var MWtsp_block = "wtsp_block"
    var _MWPBlockedReport = "wtsp_blocked"
    var _MWPTotalNumbers = "wtsp_getnumbers"
    var _MWPscroll = "wtsp_scroll"
    var _MWtspBlockSts = "wtsp_blocksts"
    var _Scrollinitial = "scroll_inital"
    var _isAppUpdated = "app_update"
    var _MWtsptitle = "app title"
    var _MWtspapp_update_id = "app_update_id"
    var _MWtspsendernumbers = "sender_numbers"
    var _AppVersion = "appversion"
    var setflag = "setflag"
    var setclickflag = "clickflag"
    var setsearchMSG = "setsearchMSG"
    var setflafinvite = "setflagInvite"
    var setrepeatwtsp = "setrepeatwtsp"
    var setclick = "setclickdate"
    var setsent = "setsentdate"
    var setsentwp = "setsentwp"
    var setcomposeID = "setcomposeID"
    var setreports = "setreports"
    var setselectuserID = "setselectuserID"
    var setProductID = "setProductID"
    var setreportnull = "setreportnull"
    var setrepstr = "setrepstr"
    var setrepreports = "setrepreports"
    var setrepselectID = "setrepselectID"
    var setdelreportnull = "setdelreportnull"
    var setblockflag = "setblockflag"
    var setsentsmsflag = "setsentsmsflag"
    var setsentrcsflag = "setsentrcsflag"



    /*------------------Sharedpreference Set Values-------------------*/
    fun setLogin(context: Context, value: Boolean) {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        val myEdit = preference.edit()

        myEdit.putBoolean(_Mlogin, value)
        myEdit.apply();
    }
    fun setFlagValue(context: Context, value: Boolean) {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        val myEdit = preference.edit()

        myEdit.putBoolean(_CHECKFlag, value)
        myEdit.apply();
    }
    fun setUsrShtname(context: Context, value: String) {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        val myEdit = preference.edit()

        myEdit.putString(_MUsershtname, value)
        myEdit.apply();
    }


    fun setFCMToken(context: Context, value: String) {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        val myEdit = preference.edit()
        myEdit.putString(_MFCMtoken, value)
        myEdit.apply();
    }

    fun setMobileNumber(context: Context, value: String) {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        val myEdit = preference.edit()
        myEdit.putString(_MMobileNumber, value)
        myEdit.apply();
    }

    fun setSMSReport(context: Context, value: Boolean) {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        val myEdit = preference.edit()
        myEdit.putBoolean(_MReportSMS, value)
        myEdit.apply();
    }

    fun setRCSReport(context: Context, value: Boolean) {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        val myEdit = preference.edit()
        myEdit.putBoolean(_MReportRCS, value)
        myEdit.apply();
    }

    fun setWPReport(context: Context, value: Boolean) {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        val myEdit = preference.edit()
        myEdit.putBoolean(_MReportWP, value)
        myEdit.apply();
    }

    fun setSearchMSG(context: Context, value: String) {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        val myEdit = preference.edit()
        myEdit.putString(_MSearchMSG, value)
        myEdit.apply();
    }

    fun setSearchMSGRCS(context: Context, value: String) {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        val myEdit = preference.edit()
        myEdit.putString(_MRCSSearchMSG, value)
        myEdit.apply();
    }

    fun setSMSSentRep(context: Context, value: String) {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        val myEdit = preference.edit()
        myEdit.putString(_MSMSSentReport, value)
        myEdit.apply();
    }

    fun setRCSSentRep(context: Context, value: String) {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        val myEdit = preference.edit()
        myEdit.putString(_MRCSSentReport, value)
        myEdit.apply();
    }


    fun setReceiverNo(context: Context, value: String) {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        val myEdit = preference.edit()
        myEdit.putString(_MReceiverNo, value)
        myEdit.apply();
    }

    fun setSMSRecNo(context: Context, value: String) {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        val myEdit = preference.edit()
        myEdit.putString(_MSMSReceiverNo, value)
        myEdit.apply();
    }

    fun setRCSRecNo(context: Context, value: String) {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        val myEdit = preference.edit()
        myEdit.putString(_MRCSReceiverNo, value)
        myEdit.apply();
    }

    fun setWPSentRep(context: Context, value: String) {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        val myEdit = preference.edit()
        myEdit.putString(_MWPSentReport, value)
        myEdit.apply();
    }

    fun setWPDelRep(context: Context, value: String) {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        val myEdit = preference.edit()
        myEdit.putString(_MWPDelReport, value)
        myEdit.apply();
    }

    fun setWPSearchNo(context: Context, value: String) {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        val myEdit = preference.edit()
        myEdit.putString(_MWPSearchNo, value)
        myEdit.apply();
    }

    fun setSMSSearchNo(context: Context, value: String) {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        val myEdit = preference.edit()
        myEdit.putString(_MSMSSearchNo, value)
        myEdit.apply();
    }

    fun setRCSSearchNo(context: Context, value: String) {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        val myEdit = preference.edit()
        myEdit.putString(_MRCSSearchNo, value)
        myEdit.apply();
    }

    fun setSMSDelRep(context: Context, value: String) {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        val myEdit = preference.edit()
        myEdit.putString(_MSMSDelReport, value)
        myEdit.apply();
    }

    fun setRCSDelRep(context: Context, value: String) {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        val myEdit = preference.edit()
        myEdit.putString(_MRCSDelReport, value)
        myEdit.apply();
    }


    fun setBack(context: Context, value: Boolean) {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        val myEdit = preference.edit()
        myEdit.putBoolean(_MSetBack, value)
        myEdit.apply();
    }


    fun setBack_rep(context: Context, value: Boolean) {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        val myEdit = preference.edit()
        myEdit.putBoolean(_MSetBack_rep, value)
        myEdit.apply();
    }

    fun setmedia_download(context: Context, value: String) {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        val myEdit = preference.edit()
        myEdit.putString(MWtsp_media, value)
        myEdit.apply();
    }

    fun setmedia_download_rcs(context: Context, value: String) {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        val myEdit = preference.edit()
        myEdit.putString(MRCS_media, value)
        myEdit.apply();
    }

    fun set_file_path_wtsp(context: Context, value: String) {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        val myEdit = preference.edit()
        myEdit.putString(MWtsp_filepath, value)
        myEdit.apply();
    }

    fun set_file_path_rcs(context: Context, value: String) {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        val myEdit = preference.edit()
        myEdit.putString(MRCS_filepath, value)
        myEdit.apply();
    }

    fun set_Block(context: Context, value: Boolean) {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        val myEdit = preference.edit()
        myEdit.putBoolean(MWtsp_block, value)
        myEdit.apply();
    }

    fun setBlockedRep(context: Context, value: String) {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        val myEdit = preference.edit()
        myEdit.putString(_MWPBlockedReport, value)
        myEdit.apply();
    }

    fun setScroll(context: Context, value: Boolean) {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        val myEdit = preference.edit()
        myEdit.putBoolean(_MWPscroll, value)
        myEdit.apply();
    }

    fun set_totalNumbers(context: Context, value: MutableList<String>) {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        val myEdit = preference.edit()
        myEdit.putString(_MWPTotalNumbers, value.toString())
        myEdit.apply();
    }

    fun set_checkblock(context: Context, value: Boolean) {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        val myEdit = preference.edit()
        myEdit.putBoolean(_MWtspBlockSts, value)
        myEdit.apply();
    }

    fun setScrollInitial(context: Context, value: Boolean) {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        val myEdit = preference.edit()

        myEdit.putBoolean(_Scrollinitial, value)
        myEdit.apply();
    }


    fun setAppupdated(context: Context, value: Boolean) {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        val myEdit = preference.edit()

        myEdit.putBoolean(_isAppUpdated, value)
        myEdit.apply();
    }

    fun setTitle(context: Context, value: String) {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        val myEdit = preference.edit()
        myEdit.putString(_MWtsptitle, value.toString())
        myEdit.apply();
    }

    fun setAppUpdateID(context: Context, value: String) {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        val myEdit = preference.edit()
        myEdit.putString(_MWtspapp_update_id, value.toString())
        myEdit.apply();
    }

    fun setSenderNumbers(context: Context, value: String) {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        val myEdit = preference.edit()
        myEdit.putString(_MWtspsendernumbers, value.toString())
        myEdit.apply();
    }

    fun setVersion(context: Context, value: String) {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        val myEdit = preference.edit()
        myEdit.putString(_AppVersion, value.toString())
        myEdit.apply();
    }

    fun setflag(context: Context, value: Boolean) {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        val myEdit = preference.edit()

        myEdit.putBoolean(setflag, value)
        myEdit.apply();
    }

    fun set_clickflag(context: Context, value: Boolean) {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        val myEdit = preference.edit()

        myEdit.putBoolean(setclickflag, value)
        myEdit.apply();
    }

    fun set_searchmsg_rep(context: Context, value: String) {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        val myEdit = preference.edit()
        myEdit.putString(setsearchMSG, value.toString())
        myEdit.apply();
    }

    fun setflag_invite(context: Context, value: Boolean) {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        val myEdit = preference.edit()

        myEdit.putBoolean(setflafinvite, value)
        myEdit.apply();
    }

    fun set_repeat_wtsp(context: Context, value: Boolean) {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        val myEdit = preference.edit()

        myEdit.putBoolean(setrepeatwtsp, value)
        myEdit.apply();
    }

    fun setclickdate(context: Context, value: Boolean) {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        val myEdit = preference.edit()

        myEdit.putBoolean(setclick, value)
        myEdit.apply();
    }

    fun setsentdate(context: Context, value: Boolean) {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        val myEdit = preference.edit()

        myEdit.putBoolean(setsent, value)
        myEdit.apply();
    }

    fun setsentwp(context: Context, value: Boolean) {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        val myEdit = preference.edit()

        myEdit.putBoolean(setsentwp, value)
        myEdit.apply();
    }

    fun set_composeID(context: Context, value: String) {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        val myEdit = preference.edit()
        myEdit.putString(setcomposeID, value.toString())
        myEdit.apply();
    }

    fun set_reports(context: Context, value: String) {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        val myEdit = preference.edit()
        myEdit.putString(setreports, value.toString())
        myEdit.apply();
    }

    fun set_selectuserID(context: Context, value: String) {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        val myEdit = preference.edit()
        myEdit.putString(setselectuserID, value.toString())
        myEdit.apply();
    }

    fun set_productID(context: Context, value: String) {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        val myEdit = preference.edit()
        myEdit.putString(setProductID, value.toString())
        myEdit.apply();
    }

    fun set_report_null(context: Context, value: Boolean) {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        val myEdit = preference.edit()

        myEdit.putBoolean(setreportnull, value)
        myEdit.apply();
    }

    fun set_rep_str(context: Context, value: String) {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        val myEdit = preference.edit()
        myEdit.putString(setrepstr, value.toString())
        myEdit.apply();
    }

    fun set_rep_reports(context: Context, value: String) {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        val myEdit = preference.edit()
        myEdit.putString(setreports, value.toString())
        myEdit.apply();
    }

    fun set_rep_selectID(context: Context, value: String) {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        val myEdit = preference.edit()
        myEdit.putString(setrepselectID, value.toString())
        myEdit.apply();
    }

    fun set_delreport_null(context: Context, value: Boolean) {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        val myEdit = preference.edit()

        myEdit.putBoolean(setdelreportnull, value)
        myEdit.apply();
    }

    fun set_blockflag(context: Context, value: Boolean) {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        val myEdit = preference.edit()

        myEdit.putBoolean(setblockflag, value)
        myEdit.apply();
    }

    fun setsentsmsflag(context: Context, value: Boolean) {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        val myEdit = preference.edit()

        myEdit.putBoolean(setsentsmsflag, value)
        myEdit.apply();
    }

    fun setsentrcsflag(context: Context, value: Boolean) {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        val myEdit = preference.edit()

        myEdit.putBoolean(setsentrcsflag, value)
        myEdit.apply();
    }




    /*------------------Sharedpreference get values-------------------*/

    fun getLogin(context: Context): Boolean {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        var getbool = preference.getBoolean(_Mlogin, false)
        println("getbool:$getbool")
        return preference.getBoolean(_Mlogin, false)
    }

    fun getFlagvalue(context: Context): Boolean {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        var getbool = preference.getBoolean( _CHECKFlag , false)
        println("getbool:$getbool")
        return preference.getBoolean( _CHECKFlag , false)
    }

    fun getUsrShtname(context: Context): String? {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        return preference.getString(_MUsershtname, "")
    }

    fun getFCMToken(context: Context): String? {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        return preference.getString(_MFCMtoken, "")
    }

    fun getMobileNumber(context: Context): String? {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        return preference.getString(_MMobileNumber, "")
    }

    fun getSMSReport(context: Context): Boolean {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        return preference.getBoolean(_MReportSMS, false)
    }

    fun getRCSReport(context: Context): Boolean {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        return preference.getBoolean(_MReportRCS, false)
    }

    fun getWPReport(context: Context): Boolean {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        return preference.getBoolean(_MReportWP, false)
    }

    fun getSearchMSG(context: Context): String? {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        return preference.getString(_MSearchMSG, "")
    }

    fun getSearchMSGRCS(context: Context): String? {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        return preference.getString(_MRCSSearchMSG, "")
    }

    fun getSMSSentReport(context: Context): String? {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        return preference.getString(_MSMSSentReport, "")
    }

    fun getRCSSentReport(context: Context): String? {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        return preference.getString(_MRCSSentReport, "")
    }

    fun getReceiverNo(context: Context): String? {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        return preference.getString(_MReceiverNo, "")
    }

    fun getSMSRecNo(context: Context): String? {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        return preference.getString(_MSMSReceiverNo, "")
    }

    fun getRCSRecNo(context: Context): String? {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        return preference.getString(_MRCSReceiverNo, "")
    }

    fun getWPSentReport(context: Context): String? {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        return preference.getString(_MWPSentReport, "")
    }

    fun getWPDelReport(context: Context): String? {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        return preference.getString(_MWPDelReport, "")
    }

    fun getSMSDelReport(context: Context): String? {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        return preference.getString(_MSMSDelReport, "")
    }

    fun getRCSDelReport(context: Context): String? {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        return preference.getString(_MRCSDelReport, "")
    }

    fun getWPSearchNo(context: Context): String? {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        return preference.getString(_MWPSearchNo, "")
    }

    fun getSMSSearchNo(context: Context): String? {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        return preference.getString(_MSMSSearchNo, "")
    }

    fun getRCSSearchNo(context: Context): String? {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        return preference.getString(_MRCSSearchNo, "")
    }

    fun getBack(context: Context): Boolean {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        return preference.getBoolean(_MSetBack, false)
    }

    fun getBack_rep(context: Context): Boolean {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        return preference.getBoolean(_MSetBack_rep, false)
    }

    fun getmedia_download(context: Context): String? {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        return preference.getString(MWtsp_media, "")
    }

    fun getmedia_download_rcs(context: Context): String? {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        return preference.getString(MRCS_media, "")
    }

    fun get_file_path_wtsp(context: Context): String? {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        return preference.getString(MWtsp_filepath, "")
    }

    fun get_file_path_rcs(context: Context): String? {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        return preference.getString(MRCS_filepath, "")
    }

    fun get_Block(context: Context): Boolean {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        return preference.getBoolean(MWtsp_block, false)
    }

    fun getWPBlockReport(context: Context): String? {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        return preference.getString(_MWPBlockedReport, "")
    }

    fun getScroll(context: Context): Boolean {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        return preference.getBoolean(_MWPscroll, false)
    }

    fun get_totalNumbers(context: Context): String? {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        return preference.getString(_MWPTotalNumbers, "")
    }

    fun get_checkblock(context: Context): Boolean {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        return preference.getBoolean(_MWtspBlockSts, false)
    }

    fun getScrollInitial(context: Context): Boolean {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        return preference.getBoolean(_Scrollinitial, false)
    }

    fun getAppupdated(context: Context): Boolean {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        return preference.getBoolean(_isAppUpdated, false)
    }

    fun getTitle(context: Context): String? {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        return preference.getString(_MWtsptitle, "")
    }

    fun getAppupdateID(context: Context): String? {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        return preference.getString(_MWtspapp_update_id, "")
    }

    fun getSenderNumbers(context: Context): String? {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        return preference.getString(_MWtspsendernumbers, "")
    }

    fun getVersion(context: Context): String? {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        return preference.getString(_AppVersion, "")
    }

    fun getflag(context: Context): Boolean {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        return preference.getBoolean(setflag, false)
    }

    fun get_click_flag(context: Context): Boolean {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        return preference.getBoolean(setclickflag, false)
    }

    fun get_searchmsg_rep(context: Context): String? {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        return preference.getString(setsearchMSG, "")
    }

    fun get_flaginvite(context: Context): Boolean {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        return preference.getBoolean(setflafinvite, false)
    }

    fun get_repeat_wtsp(context: Context): Boolean {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        return preference.getBoolean(setrepeatwtsp, false)
    }

    fun getclickdate(context: Context): Boolean {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        return preference.getBoolean(setclick, false)
    }

    fun getsentdate(context: Context): Boolean {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        return preference.getBoolean(setsent, false)
    }

    fun getsentwp(context: Context): Boolean {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        return preference.getBoolean(setsentwp, false)
    }

    fun get_composeID(context: Context): String? {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        return preference.getString(setcomposeID, "")
    }

    fun get_reports(context: Context): String? {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        return preference.getString(setreports, "")
    }

    fun get_selectuserID(context: Context): String? {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        return preference.getString(setselectuserID, "")
    }

    fun get_productID(context: Context): String? {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        return preference.getString(setProductID, "")
    }

    fun get_report_null(context: Context): Boolean {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        return preference.getBoolean(setreportnull, false)
    }


    fun get_rep_str(context: Context): String? {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        return preference.getString(setrepstr, "")
    }

    fun get_rep_reports(context: Context): String? {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        return preference.getString(setrepreports, "")
    }

    fun get_rep_selectID(context: Context): String? {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        return preference.getString(setrepselectID, "")
    }

    fun get_delreport_null(context: Context): Boolean {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        return preference.getBoolean(setdelreportnull, false)
    }
    fun get_blockflag(context: Context): Boolean {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        return preference.getBoolean(setblockflag, true)
    }
    fun getsentsmsflag(context: Context): Boolean {
        val preference = context.getSharedPreferences(
            context.resources.getString(R.string.app_name),
            Context.MODE_PRIVATE
        )
        return preference.getBoolean(setsentsmsflag, true)
    }

}

