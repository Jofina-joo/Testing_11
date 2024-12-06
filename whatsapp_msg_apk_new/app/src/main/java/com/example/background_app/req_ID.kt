/*
This kotlin file is used to get separate request ID for each process

Version : 1.0
Author : Sabena yasmin (YJ0008)
Date : 28-Mar-2024
*/
package com.example.background_app

//import the required packages and files
import android.content.Context
import java.text.SimpleDateFormat
import java.util.*

//Start Function - Get request ID
fun req_ID(context: Context): String {

    var getuser_name = SharedprefHelper.getUsrShtname(context)
    val currentDate = Calendar.getInstance()
    val currentYear = currentDate.get(Calendar.YEAR)
    val julianDate = getJulianDate(currentDate)
    val currentHour = currentDate.get(Calendar.HOUR)
    val currentMinutes = currentDate.get(Calendar.MINUTE)
    val currentSeconds = currentDate.get(Calendar.SECOND)
    val random = Random()
    val randomTwoDigitNumber = 10 + random.nextInt(90)
    val requestId = "${currentYear}${julianDate}${currentHour}${currentMinutes}${currentSeconds}_${randomTwoDigitNumber}"
    val testId = "${getuser_name}_${requestId}"
    return testId
}
//End Function - Get request ID
fun getJulianDate(calendar: Calendar): String {
    val sdf = SimpleDateFormat("D")
    return sdf.format(calendar.time)
}
