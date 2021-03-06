<?php
	$loggedIn = false;
	$username;
	//libxml_use_internal_errors(true);
	if (isset($_COOKIE["username"])){
		$username = $_COOKIE["username"];
		$loggedIn = true;
	} else {
		//Redirect to login page
		header("Location:login.php");
	}

	//Now we have username, get League info
	$league = "undetermined";
	if(isset($_GET["league"])) {
		$league = $_GET["league"];

	} else {
		//Redirect to Leagues page
		header("Location:./leagues.php");
	}
	$conn = mysqli_connect('localhost','loginUser','techhounds','fantasyfrc');
	$query = "SELECT * FROM leagues WHERE LeagueCode='" . $league . "'";
	$result = mysqli_query($conn,$query);
	if(mysqli_num_rows($result) < 1) {
		//Invalid League Code
		// TODO: Throw Error and redirect to leagues page

	}
	$leagueInfo = mysqli_fetch_assoc($result);
	$region = $leagueInfo["Region"];
	$name = $leagueInfo["LeagueName"];



?>
<html>
<head>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>


	<title>Draft System</title>
	<script src="draftAPI.js?version=12"></script>
	<style>
	body {
		margin: 0;
		padding: 0;
		box-sizing: border-box;

	}
	table {
		border-collapse: collapse;
	}
	tr {
		border: 1px solid black;
	}
	td {
		border: 1px solid black;
	}
	.nav {
		display: block;
		height: 2.5em;
		margin: 0;
		padding: 0;
		box-sizing: border-box;
		background-color: #222222;
		width: 100%;
		color: white;
	}
	.nav nav ul {
		display: block;
		list-style: none;
		margin: 0;
		padding: 0;
	}
	.nav nav ul li {
		text-align: center;
		display: inline-block;
		box-sizing: border-box;
		padding: .6em;
		height: 2.5em;
	}
	.nav nav ul li:hover {
		background-color: #4a4a4a;
	}
	.nav nav { display: block; };

	</style>
	<meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
</head>
<body>
<div class="nav">
	<nav>
		<ul>
			<li onclick="getThisYearsTeams()">
				Team Drafting For League: <?php echo $name; ?>
			</li>
			<li style="float:right">
				<?php echo $username; ?>
			</li>
		<ul>
	</nav>
</div>

<input style="margin-top:20px" type="text" id="searchbar" style="width:200px" />
<br>
<ul id="currentTeams" style="display:none"></ul>
<ul id="searchData" style="display:none"></ul>
<p id="usrVal" style="display:none;"></p>

<table id="drafting-table">
	<tr>
		<th>Nickname:</th>
		<th>Joined:</th>
		<th>Website:</th>
		<th>Team #:</th>
		<th>Average OPR</th>
		<th>Average DPR</th>
		<th>Pick Team</th>
	</tr>

</table>

	<script>
	loadOPRS();
	loadDPRS();
	/* This event happens when the user drafts a team */
	var myTurn = false;
	function isMyTurn() {
		var name = "<?php echo $username; ?>";
		var leagueCode = "<?php echo $league; ?>";
		$.ajax({
			url:'./php/draftingTurn.php',
			type: 'POST',
			data: {"username":name, "leagueCode":leagueCode},
			success: function(aData) {
				if(name == aData) {
					myTurn = true;
					populateTable();
				} else {
					myTurn= false;
					var table = document.getElementById("drafting-table");
					var length = document.getElementById("drafting-table").rows.length;
					for(var i = length - 1; i >= 0; i--) {
							var r = table.rows[i];
							r.cells[6].innerHTML = "";
					}
				}
			}
		});
	}
	var checkTurn = setInterval(isMyTurn,3000);
	function  draftAnnouncement(elementId) {
		/*  AJAX */
		if(myTurn){
			var row = elementId.parentNode.parentNode.rowIndex;
			var teamNum = document.getElementById("drafting-table").rows[row].cells[3].innerHTML;
			var name = "<?php echo $username; ?>";
			var leagueCode = "<?php echo $league; ?>";

			$.ajax({
				url: 'draftTeam.php',
				type: 'POST',
				data: { "username":name, "teamnum":teamNum, "leagueCode":leagueCode},
				success: function(aData) {
					console.log("Worked");
				}
			});
			document.getElementById("drafting-table").deleteRow(row);

			var table = document.getElementById("drafting-table");
			var length = document.getElementById("drafting-table").rows.length;
			for(var i = length - 1; i >= 0; i--) {
					var r = table.rows[i];
					r.cells[6].innerHTML = "";
			}
			isMyTurn();
		} else {
			alert("It is not your turn!")
		}

	}
	</script>

	<script>
	//LOAD ALL DRAFTING DATA
	localStorage.clear();
	var refreshTable = setInterval(populateTable,3000);

	function populateTable() {
		var username = "<?php echo $username; ?>";
		var leagueCode = "<?php echo $league; ?>";
		var region = "<?php echo $region; ?>";
		var leagueName = "<?php echo $name; ?>";
		var table = document.getElementById("drafting-table");
		var length = document.getElementById("drafting-table").rows.length;
		$.ajax({
			url: './php/getTakenTeams.php',
			type: 'POST',
			data: { "leagueCode":leagueCode },
			success: function(taken) {
					console.log(taken);
					if(localStorage.getItem("teams") == null) {
						$.ajaxSetup({
							headers : {
								'X-TBA-Auth-Key':'VG6oKsnz6E2EheeIFFkZwHjcAT66vwpttZTXWmXyPOSMyjmRyrA9Q5I8cUeiZTeJ',
								'accept':'application/json'
							}
						});
						var rCode;
						if(region == "IND") {
							rCode = "2020in";
						}
						$.getJSON('https://cors-anywhere.herokuapp.com/'+ 'https://www.thebluealliance.com/api/v3/district/' + rCode +'/teams',
							function(aData) {
								var teams = [];
								for(var j = 0; j < aData.length; j++) {
									var tnum = parseInt(aData[j].team_number);
									var found = false;
									for(var i = 0; i < taken.length; i++) {
										if(parseInt(taken[i], 10) == tnum) {
											found = true;
											break;
										}
									}
									if(!found) {
										var r = table.insertRow(j + 1);
										var nickname = r.insertCell(0);
										var joined = r.insertCell(1);
										var website = r.insertCell(2);
										var teamnum = r.insertCell(3);
										var avgOPR = r.insertCell(4);
										var avgDPR = r.insertCell(5);
										var draft = r.insertCell(6);

										nickname.innerHTML = aData[j].nickname;
										joined.innerHTML = aData[j].rookie_year;
										website.innerHTML = aData[j].website;
										teamnum.innerHTML = tnum;
										var oprNum = getTeamOPR(tnum);
										var dprNum = getTeamDPR(tnum);

										avgOPR.innerHTML = oprNum;
										avgDPR.innerHTML = dprNum;
										if(myTurn) {
										draft.innerHTML = "<button id='" + tnum + "' onclick='draftAnnouncement(this)'>Draft Team</button>";
										}
										localStorage.setItem(tnum + ":OPR",oprNum);
										localStorage.setItem(tnum + ":DPR",dprNum);
										localStorage.setItem(tnum + ":nickname",aData[j].nickname);
										localStorage.setItem(tnum + ":joined",aData[j].rookie_year);
										localStorage.setItem(tnum + ":website",aData[j].website);
										teams.push(tnum);
									}
								}
							localStorage.setItem("teams",JSON.stringify(teams));
						});
					} else {
						//Clear Table

						var ln = table.rows.length;
						for(var i = ln - 1; i > 0; i--) {
							table.deleteRow(i);
						}
						var teams = JSON.parse(localStorage.getItem("teams"));

							var rownum = 1;
							for(var i = 0; i < teams.length; i++) {
								var teamnum = teams[i];
								var found = false;
								// YOU FORGOT TO PARSE THE JSON
								var takenArr = JSON.parse(taken);
								for(var j = 0; j < takenArr.length; j++) {
									// CLEAN CODE BECAUSE WHY NOT
									var takenTeamNum = parseInt(takenArr[j],10);
									if(takenTeamNum == teamnum) {
										found = true;
										// IT WORKS WITHOUT IT NOW MATTHEW AND NOW U DIDNT BREAK THE DRAFTING BUTTON FEATURE
										break;
									}
								}
								if(!found) {
									var r = table.insertRow(rownum++);
									var nickname = r.insertCell(0);
									var joined = r.insertCell(1);
									var website = r.insertCell(2);
									var tm = r.insertCell(3);
									var avgOPR = r.insertCell(4);
									var avgDPR = r.insertCell(5);
									var draft = r.insertCell(6);
									if(myTurn) {
										draft.innerHTML = "<button id='" + teamnum + "' onclick='draftAnnouncement(this)'>Draft Team</button>";
									}
									tm.innerHTML = teamnum;
									nickname.innerHTML = localStorage.getItem(teamnum + ":nickname");
									joined.innerHTML = localStorage.getItem(teamnum + ":joined");
									website.innerHTML = localStorage.getItem(teamnum + ":website");
									avgOPR.innerHTML = localStorage.getItem(teamnum + ":OPR");
									avgDPR.innerHTML = localStorage.getItem(teamnum + ":DPR");
								}
							}
					}
				}
			});

		}


	</script>
</body>
</html>
