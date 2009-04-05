/*
	AdvancedCountDown.js
	Written By: Yahav Braverman, 2008
	
	ANY CHANGE IN THE CODE BELOW MIGHT CAUSE UNEXPECTED PROBLEMS.
	PLEASE USE THE SAMPLE CODE THAT COME WITH THIS FILE.
*/

var _arrCountDownContainers = new Array();
var _arrCountDownSeconds = new Array();
var _arrCountDownCallbacks = new Array();

var _countDownTimer = 0;

function _cdt_CountDownTick()
{
	var activeCount = 0;
	for (var key in _arrCountDownSeconds)
	{
		var curSeconds = _arrCountDownSeconds[key];
		if (curSeconds < 0)
			continue;
		
		if (curSeconds == 0)
		{
			_cdt_TimeOver(key);
			continue;
		}
		
		_cdt_ApplyCountdownText(key);
		activeCount++;
	}
	
	if (activeCount > 0)
		_countDownTimer = window.setTimeout("_cdt_CountDownTick()", 100);
}

function _cdt_TimeOver(key)
{
	_arrCountDownSeconds[key] = -1;
	var strCallback = _arrCountDownCallbacks[key];
	if (strCallback && strCallback.length > 0)
	{
		eval(strCallback + "();");
	}
}

function ActivateCountDown(strContainerID, initialValue, strCallback)
{
	if (typeof initialValue == "undefined")
	{
		if (_arrCountDownSeconds[strContainerID] && _arrCountDownSeconds[strContainerID] < 0)
		{
			_arrCountDownSeconds[strContainerID] = _arrCountDownSeconds[strContainerID] * -1;
			_arrCountDownContainers[strContainerID].setAttribute("initial_timer_value", _arrCountDownSeconds[strContainerID] + "");
			_arrCountDownContainers[strContainerID].setAttribute("activation_time", _cdt_GetCurrentTime() + "");
			
			RestartCountdownTimer(1);
		}
		return;
	}
	
	var objContainer = document.getElementById(strContainerID);
	if (!objContainer)
	{
		alert("count down error: container does not exist: " + strContainerID + "\nmake sure html element with this ID exists");
		return;
	}
	
	objContainer.setAttribute("activation_time", _cdt_GetCurrentTime() + "");
	objContainer.setAttribute("initial_timer_value", initialValue + "");
	
	_arrCountDownContainers[strContainerID] = objContainer;
	_arrCountDownCallbacks[strContainerID] = strCallback;
	_cdt_ApplyCountdownText(strContainerID);
	
	RestartCountdownTimer(1000);
}


function RestartCountdownTimer(value)
{
	if (_countDownTimer)
		window.clearTimeout(_countDownTimer);
	_countDownTimer = window.setTimeout("_cdt_CountDownTick()", value);
}

function DeactivateCountDown(strContainerID)
{
	if (_arrCountDownSeconds[strContainerID] && _arrCountDownSeconds[strContainerID] > 0)
	{
		_arrCountDownSeconds[strContainerID] = _arrCountDownSeconds[strContainerID] * -1;
	}
}

function DeactivateAllCountdowns()
{
	for (var key in _arrCountDownSeconds)
	{
		DeactivateCountDown(key);
	}
}

function ActivateAllCountdowns()
{
	for (var key in _arrCountDownSeconds)
	{
		ActivateCountDown(key);
	}
}

function SetInitialTime(strContainerID, initialValue)
{
	var nValue = parseInt(initialValue);
	if (isNaN(nValue))
	{
		alert("invalid number: " + initialValue);
		return;
	}

	if (nValue < 0)
		nValue = 0;
	
	var objContainer = _arrCountDownContainers[strContainerID];
	if (objContainer)
	{
		objContainer.setAttribute("activation_time", _cdt_GetCurrentTime() + "");
		objContainer.setAttribute("initial_timer_value", nValue + "");
	}
}

function GetCurrentTime(strContainerID)
{
	var objContainer = _arrCountDownContainers[strContainerID];
	if (objContainer)
	{
		return _cdt_GetRealSeconds(objContainer);
	}
	return 0;
}

function _cdt_ApplyCountdownText(strContainerID)
{
	//get container:
	var objContainer = _arrCountDownContainers[strContainerID];

	//get format:
	var strFormat = objContainer.getAttribute("time_format");
	var blnCustomFormat = true;
	if (!strFormat || strFormat.length == 0)
	{
		strFormat = "%h:%m:%s";
		blnCustomFormat = false;
	}

	//get real seconds
	var seconds = _cdt_GetRealSeconds(objContainer);
	
	//store:
	_arrCountDownSeconds[strContainerID] = seconds;
	
	//build text:
	var strText = "";
	
	//time over?
	var strFinishTime = objContainer.getAttribute("finish_value");
	if (strFinishTime && strFinishTime.length > 0)
	{
		var nFinishTime = parseFloat(strFinishTime);
		if (!isNaN(nFinishTime) && seconds < nFinishTime)
		{
			_cdt_TimeOver(strContainerID);
			return;
		}
	}

	//raw?
	if (strFormat == "RAW")
	{
		strText = parseFloat(seconds.toFixed(2));
	}
	else
	{
		//get minutes:
		var minutes = parseInt(seconds / 60);
		
		//shrink:
		seconds = (seconds % 60);
		
		//get hours:
		var hours = parseInt(minutes / 60);
		
		//shrink:
		minutes = (minutes % 60);
		
		//get days:
		//var days = parseInt(hours / 24);
	
		//shrink:
		//hours = (hours % 24);
		
		//need to add zero?
		//if (!blnCustomFormat)
		//{
			hours = AddZero(hours);
			minutes = AddZero(minutes);
			seconds = AddZero(seconds);
		//}
		strText = strFormat.replace("%h", hours + "").replace("%m", minutes + "").replace("%s", seconds + "");
	}
	
	//apply:
	objContainer.innerHTML = strText;
}

function AddZero(num)
{
	return ((num >= 0)&&(num < 10))?"0"+num:num+"";
}

function _cdt_GetCurrentTime()
{
	var objDate = new Date();
	return objDate.getTime();
}

function _cdt_GetRealSeconds(objContainer)
{
	var nCurrentTime = _cdt_GetCurrentTime();
	var nActivationTime = parseInt(objContainer.getAttribute("activation_time"));
	var nInitialValue = parseFloat(objContainer.getAttribute("initial_timer_value"));
	var nMiliSecondsDiff = (nCurrentTime - nActivationTime);
	var nTotalDifference = parseInt(nMiliSecondsDiff / 1000);
	var strSecondValue = objContainer.getAttribute("second_value");
	if (strSecondValue && strSecondValue.length > 0)
	{
		var nSecondValue = parseFloat(strSecondValue);
		if (!isNaN(nSecondValue))
			nTotalDifference = parseFloat(nTotalDifference) * nSecondValue;
	}
	return (nInitialValue - nTotalDifference);
}