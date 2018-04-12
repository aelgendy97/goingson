<?php
    session_start();    // Start the session

    // Don't allow the user to access the map without logging in first
    if(!isset($_SESSION['user'])){
        header('Location: index.php');
    }

?>

<!DOCTYPE html>
<html ng-app="myApp">
    <head>
        <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
        <link type='text/css' rel='stylesheet' href='style/style.css'/>
        <link type='text/css' rel='stylesheet' href='style/normalize.css'/>
        <title>GoingZ On</title>

        <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
        <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
        <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCzMHc5MQBw4UrHMCKTCVmwSngKzo1Kh6I"></script>
        <script src="node_modules/angular/angular.js"></script>
        <script src="js/app.js"></script>
    </head>
    <body ng-controller="baseCtrl">
        <ul class="top-nav-bar" ng>
            <li class="tab-left">
                <span onclick="toggleNav()"><a><image src="images/drawer.png"></image></a></span>
            </li>
            <li class="tab-middle">
                <h1>GoingZ On</h1>
            </li>
        </ul>
        
        <div id="mySidenav" class="sidenav">
            <a class="top" href="#" id="opener">Events</a>
            <a href="#" ng-click="updateProfile()">Profile</a>
            <a href="#" ng-click="populateMarkers(null)">Refresh</a>
            <a href="logout.php">Logout</a>
        </div>
        
        <div id="profileDialog" title="Edit Profile" style="display:none">
            <form>
                <h3>Username: </h3>
                <input type="text" id="username" autocomplete="off" />
                <h3>Password: </h3>
                <input type="password" id="password" autocomplete="new-password" />
                <h3>Email: </h3>
                <input type="text" id="email" autocomplete="off" />
            </form>
        </div>
        
        <div id="errorsDialog" title="Update errors">
            
        </div>

        <div id="map"></div>

        <div id="eventsDialog" title="Events">
            <ul class="events">
                <li class="entry" ng-repeat="event in events">
                    <img class="img" src="images/drawer.png" />
                    <h3 class="title">{{event.title}}</h3>
                    <p class="text">{{event.description}}</p>
                </li>
            </ul>
            <button ng-click="populateMarkers(currentUserId)">My Events</button>
            <button ng-click="populateMarkers(null)">All Events</button>
        </div>
        
        <div id="eventDialog" title="Event">
            <h3>Title</h3>
            <p>{{displayedEvent.title}}</p>
            <h3>Description</h3>
            <p>{{displayedEvent.description}}</p>
            <button ng-if="canVote && !eventEnded" ng-click="openVoteDialog(displayedEvent.eventid)">Can You Verify This Event?</button>
            <div id="votingDialog" title="Verify Event">
                <p>Is this event happening?</p>
            </div>
            <button ng-if="displayedEvent.userid == currentUserId" ng-click="openEditDialog()">Edit</button>
            <button ng-if="displayedEvent.userid == currentUserId && canSetEndDate && canSetDuration" ng-click="openTimerDialog(displayedEvent.eventid)">Set event duration</button>
            <div id="eventTimer" title="Set Event Duration">
                <p>Duration of the event:</p>
                <input type="number" id="hourVal" min="0" value="0" > Hour(s)
                <br/>
                <input type="number" id="minVal" min="0" max="59" value="0"> Minute(s)
                <p>Please note that the event duration can't be changed once it is set.</p>
            </div>
            <button ng-if="displayedEvent.userid == currentUserId && canSetEndDate && !counterStarted && !canSetDuration" ng-click="startCountdown()">Start Event</button>
            <div id="countdown">
                <span id="time"></span>
                <span id="finishTime"></span>
            </div>
        </div>
        
        <div id="createEventDialog" title="Create Event">
            <form>
                <h3>Title</h3>
                <input ng-model="newEventTitle"/>
                <h3>Description</h3>
                <input ng-model="newEventDesc"/>
                <h3>Type</h3>
                <select value="Event" ng-model="newEventType">
                    <option value="Event">Event</option>
                    <option value="Question">Question</option>
                </select>
                <input style="display: block;" ng-click="createEvent(newEventTitle, newEventDesc, newEventType)" type="submit"/>
            </form>
        </div>
        
        <div id="editEventDialog" title="Create Event">
            <form>
                <h3>Title</h3>
                <input ng-model="displayedEvent.title"/>
                <h3>Description</h3>
                <input ng-model="displayedEvent.description"/>
                <h3>Type</h3>
                <select value="Event" ng-model="displayedEvent.typeId">
                    <option value="Event">Event</option>
                    <option value="Question">Question</option>
                </select>
<!--                <input style="display: block;" ng-click="createEvent(newEventTitle, newEventDesc, newEventType)" type="submit"/>-->
            </form>
        </div>
        <script>
            var currentUserId = "<?php echo $_SESSION["userid"]; ?>";
            var currentUsername = "<?php echo $_SESSION["user"]; ?>";

            function toggleNav() {
                if(document.getElementById("mySidenav").style.width == 0 || document.getElementById("mySidenav").style.width == "0px"){
                    document.getElementById("mySidenav").style.width = "200px";
                } else {
                    document.getElementById("mySidenav").style.width = "0px";
                }
            }

            $( function() {
                $( "#eventsDialog" ).dialog({
                    autoOpen: false,
                    show: false,
                    hide: false,
                    height: 600,
                    width: 600
                });
                $( "#eventDialog" ).dialog({
                    autoOpen: false,
                    show: false,
                    hide: false,
                    height: 300,
                    width: 300
                });
                $( "#createEventDialog" ).dialog({
                    autoOpen: false,
                    show: false,
                    hide: false,
                    height: 300,
                    width: 300
                });
                $( "#votingDialog" ).dialog({
                    autoOpen: false,
                    show: false,
                    hide: false,
                    resizable: false,
                    draggable: false,
                    modal: true
                });
                $( "#editEventDialog" ).dialog({
                    autoOpen: false,
                    show: false,
                    hide: false,
                    height: 300,
                    width: 300
                });
                $( "#eventTimer" ).dialog({
                    autoOpen: false,
                    show: false,
                    hide: false,
                    height: 300,
                    width: 300
                });
                $( "#profileDialog" ).dialog({
                    autoOpen: false,
                    show: false,
                    hide: false,
                    height: 380,
                    width: 300
                });
                $( "#errorsDialog" ).dialog({
                    autoOpen: false,
                    show: false,
                    hide: false,
                    height: 450,
                    width: 350
                });
                $( "#opener" ).on( "click", function() {
                    $( "#eventsDialog" ).dialog( "open" );
                });
            } );
        </script>
    </body>
</html>