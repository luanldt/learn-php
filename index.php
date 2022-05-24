<head>
	<style>
		.text-center {
			text-align: center;
		}
		table form {
			margin: 0;
			float: left;
			margin-left: 3px;
		}
    .pagination {
      display: flex;
      justify-content: space-between;
    }
		.pagination-list {
			list-style-type: none;
			display: flex;
			padding: 0;
			text-align: right;
			justify-content: end;
      margin: 0;
		}
		.pagination li {
			margin-left: 5px;
		}
	</style>
</head>
<?php
	
	if (!isset($_SESSION)) {
		session_start();
	}
	if (isset($_SESSION['contacts'])) {
		$contacts = $_SESSION['contacts'];
	} else {
		$contacts = array();
		$_SESSION['contacts'] = $contacts;
	}
?>
<?php
	if (isset($_GET['action'])) {
		$action = $_GET['action'];
		switch ($action) {
			case 'add':
				if (isset($_POST['submit']) && isset($_POST['csrf_token'])) {
					$csrf_token = $_POST['csrf_token'];
					if ($csrf_token !== $_SESSION['token']) {
						header("Location: http://localhost:8080/dashboard/",true, 301);
						exit;
					}
					if (isset($_POST['uid']) && $_POST['uid'] !== '') {
						if (isset($_POST['name']) && isset($_POST['age']) && isset($_POST['phone'])) {
							$uid = $_POST['uid'];
							for ($x = 0; $x < count($contacts); $x++) {
								if ($contacts[$x]['uid'] === $uid) {
									$name = $_POST['name'];
									$age = $_POST['age'];
									$phone = $_POST['phone'];
									$contact = $contacts[$x];
									$contact["name"] = trim($name);
									$contact["age"] = trim($age);
									$contact["phone"] = trim($phone);
									$contacts[$x] = $contact;
									$_SESSION['contacts'] = $contacts;
									break;
								}
							}
						}
					} else {
						if (isset($_POST['name']) && isset($_POST['age']) && isset($_POST['phone'])) {
							$name = $_POST['name'];
							$age = $_POST['age'];
							$phone = $_POST['phone'];
							array_push($contacts, array(
								"uid" => uniqid(),
								"name" => trim($name),
								"age" => trim($age),
								"phone" => trim($phone)
							));
							$_SESSION['contacts'] = $contacts;
						}
					}
				}
				unset($_SESSION['contact']);
				break;
			case 'update':
				if (isset($_POST['submit']) && isset($_POST['csrf_token'])) {
					$csrf_token = $_POST['csrf_token'];
					if ($csrf_token !== $_SESSION['token']) {
						header("Location: http://localhost:8080/dashboard/",true, 301);
						exit;
					}
					if (isset($_POST['uid'])) {
						$uid = $_POST['uid'];
						for ($x = 0; $x < count($contacts); $x++) {
							if ($contacts[$x]['uid'] === $uid) {
								$_SESSION['contact'] = $contacts[$x];
								break;
							}
						}
					}
				}
				break;
			case 'delete':
				$csrf_token = $_POST['csrf_token'];
				if ($csrf_token !== $_SESSION['token']) {
					header("Location: http://localhost:8080/dashboard/",true, 301);
					exit;
				}
				if (isset($_POST['uid'])) {
					$uid = $_POST['uid'];
					for ($x = 0; $x < count($contacts); $x++) {
						if ($contacts[$x]['uid'] === $uid) {
							array_splice($contacts, $x, 1);
							break;
						}
					}
				}
				$_SESSION['contacts'] = $contacts;
				unset($_SESSION['contact']);
				break;
			default:
				# code...
				break;
		}
		header("Location: http://localhost:8080/dashboard/",true, 301);
		exit;
	}
?>
<form action="?action=add" method="post">
	<?php 
		$_SESSION['token'] = bin2hex(random_bytes(35));
		if (isset($_SESSION['contact']))
			$contact = $_SESSION['contact'];
	?>
	<input type="hidden" value="<?php echo $_SESSION['token'] ?>" name="csrf_token" />
	<fieldset>
		<legend>Add Contact</legend>
		<table>
			<tr>
				<td><label>Name</label></td>
				<td><input type="text" value="<?php if (isset($contact)) { echo $contact['name']; } ?>" required name="name"/></td>
			</tr>
			<tr>
				<td><label>Age</label></td>
				<td><input type="number" value="<?php if (isset($contact)) { echo $contact['age']; } ?>" name="age"/></td>
			</tr>
			<tr>
				<td><label>Phone</label></td>
				<td><input type="text" value="<?php if (isset($contact)) { echo $contact['phone']; } ?>" required name="phone"/></td>
			</tr>
			<tr>
				<td><input type="hidden" value="<?php if (isset($contact)) { echo $contact['uid']; } ?>" name="uid"/></td>
				<td><button name="submit" type="submit">Save</button></td>
			</tr>
		</table>
	</fieldset>
	<?php 
		if(isset($_SESSION['contact'])) { // Change Here
			unset($_SESSION['contact']);
		}
	?>
</form>
<hr/>
<h1>Contact Management</h1>
<table width="100%" border="1" cellspacing="0" cellpadding="0">
	<thead>
		<th width="2%">#</th>
		<th>Name</th>
		<th>Age</th>
		<th>Phone</th>
		<th width="8%">Action</th>
	</thead>
	<tbody>
	<?php
		$size = 10;
		if (isset($_GET['page'])) {
			$page = $_GET['page'];
		} else {
			$page = 1;
		}
		$total = count($contacts);
		$totalPage = ceil($total / $size);
    $offset = ($page - 1) * $size;
		$items = array_slice($contacts, $offset, $size);
		foreach ($items  as $index=>$contact) {
			echo "<tr>";
			echo "	<td class='text-center'>" . ($offset + $index + 1) . "</td>";
			echo "	<td>" . $contact["name"] . "</td>";
			echo "	<td class='text-center'>" . $contact["age"] . "</td>";
			echo "	<td>" . $contact["phone"] . "</td>";
			echo "	<td>";
			echo "		<form action='?action=update' method='POST'>";
			echo " 			<input type='hidden' value='" . $_SESSION['token'] . "' name='csrf_token' />";
			echo " 			<input type='hidden' value='" . $contact["uid"] . "' name='uid' />";
			echo "			<button type='submit' name='submit'>UPDATE</button>";
			echo "		</form>";
			echo "		<form action='?action=delete' method='POST'>";
			echo " 			<input type='hidden' value='" . $_SESSION['token'] . "' name='csrf_token' />";
			echo " 			<input type='hidden' value='" . $contact["uid"] . "' name='uid' />";
			echo "			<button type='submit' name='submit'>DELETE</button>";
			echo "		</form>";
			echo " </td>";
			echo "</tr>";
		}
	?>
	</tbody>
</table>
  <div class="pagination">
  <span>
    <?php
      if (count($items) === $total || ($offset + count($items)) === $total) {
        echo "Show item " . ($offset + count($items)) . " of " . $total . " items.";
      } else {
        echo "Show item " . $offset + 1 . " - " . min($offset + $size, $total) . " of " . $total . " items.";
      }
    ?>
  </span>
  <ul class="pagination-list">
    <?php
      if ($page > $totalPage) {
        echo "<a href='?page=" . max($page - 1, 1) . "'><<</a>";
      } else {
        echo "<<";
      }
    ?>
    <?php 
      for($i = 1; $i <= $totalPage; $i++) {
        echo "<li><a href='?page=" . $i . "'>" . $i . "</a><li>";
      }
    ?>
    <li>
      <?php
        if ($page < $totalPage) {
          echo "<a href='?page=" . min($page + 1, $totalPage) . "'>>></a>";
        } else {
          echo ">>";
        }
      ?>
    </li>
  </ul>
  </div>
