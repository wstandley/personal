// Function to display the table in the index.HTML page
function showTable(data) {
  // retrieve the table element from HTML
  const table = document
    .getElementById("table")
    .getElementsByTagName("tbody")[0];

  // refresh the table to nothing so data doesn't keep being added if already present
  table.innerHTML = "";

  // loop through each item in the data array
  data.forEach((item) => {
    // Create the row and its cells
    let row = table.insertRow();
    let console_cell = row.insertCell();
    let series_title_cell = row.insertCell();
    let year_cell = row.insertCell();
    let publish_cell = row.insertCell();
    let game_genre_cell = row.insertCell();
    let trash_cell = row.insertCell();

    // Add content to those cells
    console_cell.textContent = item.ConsoleName;
    series_title_cell.textContent = item.Series + " " + item.Title;
    year_cell.textContent = item.ReleaseYear;
    publish_cell.textContent = item.Publisher;
    game_genre_cell.textContent = item.Genres || "";

    if (item.Series == item.Title) {
      series_title_cell.textContent = item.Series;
    }

    // The Creation of the trash icon Cells
    let trashIcon = document.createElement("img");
    trashIcon.src = "images/trash-icon.png";
    trashIcon.alt = "Trash Button";
    trashIcon.classList.add("trash-icon");
    trashIcon.dataset.GameID = item.GameID; // Stores the GameID data so we can remove from table

    trash_cell.appendChild(trashIcon);
  });
}

function handleErrors(response) {
  if (!response.ok) {
    // normally this should throw an error
    console.log(response.status + ": " + response.statusText);
  }
  return response.json();
}

// This function gets the data, checks for errors, and sees if there is a way to specifically sort the data
function getSort() {
  var order = "";
  if (this.name) {
    order = this.name;
  }

  var request = new XMLHttpRequest();

  if (order == "") {
    fetch("sql/get_games.php")
      .then((response) => response.json())
      .then((data) => showTable(data));
  } else {
    fetch("sql/get_games.php", {
      method: "POST",
      // headers: { "Content-Type" : "application/json" },
      headers: {
        "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8",
      },
      // body: { 'order' : order }
      body: "order=" + order,
    })
      .then((response) => response.json())
      .then((data) => showTable(data));
  }
}

// Function to retrieve genres for dropdown on index page
function populateGenres() {
  const genreSelectionDiv = document.getElementById("genres"); // Change to a div

  fetch("sql/get_genres.php")
    .then((response) => response.json())
    .then((genres) => {
      genreSelectionDiv.innerHTML = "";
      genres.forEach((genre) => {
        const checkbox = document.createElement("input");
        checkbox.type = "checkbox";
        checkbox.name = "genres[]"; // Sets array
        checkbox.value = genre.GenreID;
        checkbox.id = `genre-${genre.GenreID}`;

        const label = document.createElement("label");
        label.htmlFor = `genre-${genre.GenreID}`;
        label.textContent = genre.Genre;

        genreSelectionDiv.appendChild(checkbox);
        genreSelectionDiv.appendChild(label);
        genreSelectionDiv.appendChild(document.createElement("br"));
      });
    });
}

function deleteGame(gameID) {
  fetch("sql/remove_game.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8",
    },
    body: "game_id=" + gameID, // Sends GameID to the remove_game.php file
  })
    .then(handleErrors)
    .then((response) => {
      if (response.success) {
        const deleteRow = document
          .querySelector(`[data--game-i-d="${gameID}"]`)
          .closest("tr");
        if (deleteRow) {
          deleteRow.remove();
        } else {
          getSort();
        }
      } else {
        console.error(`Failed to delete game with ID ${gameID}.`);
        alert("Error deleting game."); // Inform the user of the error
      }
    })
    .catch((error) => console.error("Error during delete request:", error));
}

// looks for button click to specify a way to sort the data
document.addEventListener("DOMContentLoaded", () => {
  const seriesButton = document.getElementById("series-button");
  seriesButton.addEventListener("click", getSort);
  seriesButton.name = "series";

  const titleButton = document.getElementById("title-button");
  titleButton.addEventListener("click", getSort);
  titleButton.name = "title";

  const yearButton = document.getElementById("year-button");
  yearButton.addEventListener("click", getSort);
  yearButton.name = "year";

  const consoleButton = document.getElementById("console-button");
  consoleButton.addEventListener("click", getSort);
  consoleButton.name = "console";

  const publisherButton = document.getElementById("publisher-button");
  publisherButton.addEventListener("click", getSort);
  publisherButton.name = "publisher";

  const genreButton = document.getElementById("genre-button");
  genreButton.addEventListener("click", getSort);
  genreButton.name = "genre";

  const tableBody = document.querySelector("#table tbody");
  tableBody.addEventListener("click", (event) => {
    if (event.target.classList.contains("trash-icon")) {
      const gameToDelete = event.target.dataset.GameID;
      if (confirm(`Are you sure you want to delete this game?`)) {
        deleteGame(gameToDelete);
      }
    }
  });

  populateGenres(); // Get Genres for dropdown
  getSort();
});
