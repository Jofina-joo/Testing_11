/*
This kotlin file is used to set timeout for API

Version : 1.0
Author : Sabena yasmin (YJ0008)
Date : 28-Mar-2024
*/
package com.example.background_app

//import the required packages and files
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.withContext
import okhttp3.*
import org.json.JSONObject
import java.io.IOException
import java.util.concurrent.TimeUnit

class Common_API {
    private val client = OkHttpClient.Builder()//Http request
        .connectTimeout(90000, TimeUnit.MILLISECONDS) //Set timeout until response came
        .readTimeout(90000, TimeUnit.MILLISECONDS)
        .build()
     suspend fun httpPost(
        method: String,
        apiUrl: String,
        bodyJson: JSONObject
    ): Request.Builder? {
        return withContext(Dispatchers.IO) {
            try {
                //check if post method
                if (method == "post") {
                    // Convert the JSONObject to a String
                    val jsonString = bodyJson.toString()
                    val requestBuilder = Request.Builder()
                        .url(apiUrl)
                        .post(RequestBody.create(MediaType.parse("application/json; charset=UTF-8"), jsonString))
                    requestBuilder
                } else {
                    null
                }
            } catch (e: IOException) {
                // Handle network or request error
                e.printStackTrace()
                null
            } catch (e: Exception) {
                // Handle other exceptions
                e.printStackTrace()
                null
            }
        }
    }
}
