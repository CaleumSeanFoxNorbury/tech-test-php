// js functions 

import { request } from "./ajax.js";

// variable states
let currentPage = 1;
let totalPages = 1;
let perPage = 10;
let amenitieOptions = [];
let data;    

export async function paginatedControl(operation = "increment"){
    if(operation === "increment"){
        currentPage++;
    } else {
        currentPage--;
    }

    await renderData();
}

export async function renderData(filters = undefined, filterValues = undefined){
    // show preloader    
    document.getElementById("preloader").style.display = "block";

    try{
        // clear filters and options
        amenitieOptions = [];

        // apply filter url params
        let filterStr = '';
        if(filters && filterValues){
            filters.forEach(filter => {
                filterStr += `&${filter}=${filterValues[filter]}`
            });
        }

        let response = await request(`http://localhost:8080/?route=/api/property-list&page=${currentPage}&perPage=${perPage}`+(filters && filterValues ? filterStr : ``));
        if(response){
            data = JSON.parse(response.data);
            currentPage = parseInt(response.pagination.currentPage);
            totalPages = parseInt(response.pagination.totalPages);
        
            let prevControl = document.getElementById("paginatedControlPrev");
            let nextControl = document.getElementById("paginatedControlNext");
            document.getElementById("pageCounts").innerHTML = currentPage+"/"+totalPages;
        
            // pagination controls based on current page
            if (currentPage > 1) {
                prevControl.style.display = "";
            } else {
                prevControl.style.display = "none";
            }
        
            if (currentPage === totalPages) {
                nextControl.style.display = "none";
            } else {
                nextControl.style.display = "";
            }
        
            // re-render data
            renderProperties(data);
        }
    } catch(error){
        // error gracefully
        console.error(error);
    }
}

export async function setCoordinates(){
    try{
        await request(`http://localhost:8080/?route=/api/amend-coordinates`);
    } catch(error){
        // error gracefully
        console.error(error);
    }
}

export async function filterData(
    proximityFilter = undefined, 
    amenitieFilter = undefined,
    priceRangeFilter = undefined,
    roomCountFilter = undefined,
){
    let filters = [];
    let filterData = {};

    if(proximityFilter && proximityFilter !== "s"){
        filters.push("proximity");
        filterData["proximity"] = proximityFilter;
    }
    if(amenitieFilter){
        filters.push("amenitie");
        filterData["amenitie"] = amenitieFilter;
    }
    if(priceRangeFilter){
        filters.push("price");
        filterData["price"] = priceRangeFilter;
    }
    if(roomCountFilter){
        filters.push("roomCount");
        filterData["roomCount"] = roomCountFilter;
    }

    await renderData(filters, filterData);
}

// create property card els
function createPropertyCard(property) {
    let propertyElement = document.createElement("div");
    propertyElement.classList.add("p5", "m5", "card-shadow");
    propertyElement.style.border = "1px solid black";
    propertyElement.style.borderRadius = "20px";
    propertyElement.style.width = "40%";

    // push all amenitie options for filtering
    amenitieOptions.push(...property.amenities);

    propertyElement.innerHTML += `
    <div>
        <div class="row jcspace-between">
            <div class="col jccenter wrapwrap mb4" style="max-width: calc(100% - 120px); overflow: hidden;">
                <h1>${property.title}</h1>
                <h2 class="mt2" style="font-size: 18px; color: #5e5df0;">${property.address}</h2>
            </div>
            <img class="img-expand property-img" style="height: 100px; width: 100px;" src="/data/images/${property.image}" onerror="this.src='https://img.freepik.com/free-vector/illustration-gallery-icon_53876-27002.jpg'" />
        </div>
        <div class="col">
            <div>${property.amenities.join(", ")}</div>
            <div class="row aicenter jcspace-between">
                <div class="row aicenter jccenter my1"><i class="fa fa-bath m1" aria-hidden="true"></i> Bathrooms: X${property.bathrooms}</div>
                <div class="row aicenter jccenter my1"><i class="fa fa-bed m1" aria-hidden="true"></i> Bedrooms: X${property.bedrooms}</div>
                <div class="row aicenter jccenter my1"><i class="fa fa-address-book m1" aria-hidden="true"></i> Reception: X${property.receptionRooms}</div>
            </div>
            <div class="row aicenter jcspace-between">
                <div><span style="font-weight: bold;">Sold by:</span> <span style="color: #5e5df0;">${property.soldBy}</span></div>    
                <h1>${property.price}</h1>    
            </div>
        </div>
    </div>`;

    return propertyElement;
}

// render property cards
function renderProperties(data) {
    let propertyList = document.getElementById("propertyListAnchor");
    // clear children
    propertyList.innerHTML = "";

    // no data
    if(!data || data.length === 0){
        let noDataWrapper = document.createElement("div");
        noDataWrapper.classList = "row aicenter jccenter m5";
        noDataWrapper.style.height = "100%";
        noDataWrapper.style.width = "100%";

        let noDataE = document.createElement("h1");
        noDataE.style.fontSize = "40px";
        noDataE.style.margin = "50px";
        noDataE.style.textAlign = "center";
        noDataE.style.width = "100%";
        noDataE.style.color = "gray";
        noDataE.innerHTML = "No Properties Match";

        noDataWrapper.append(noDataE);
        propertyList.append(noDataWrapper);

        // close preloader
        document.getElementById("preloader").style.display = "none";
        document.getElementById("paginatedControlWrapper").style.visibility = "hidden";
        return;
    }
    
    // show paginated controls
    document.getElementById("paginatedControlWrapper").style.visibility = "visible";

    // Loop through data and create property card els
    data.forEach(property => {
        propertyList.appendChild(createPropertyCard(property));
    });

    amenitieOptions.forEach(optionText => {
        const li = document.createElement("li");

        const label = document.createElement("label");
        label.classList = "row aicenter";

        const input = document.createElement("input");
        input.setAttribute("type", "checkbox");
        input.setAttribute("name", optionText);
        input.setAttribute("value", optionText);

        const text = document.createElement("div");
        text.innerHTML = optionText;

        li.appendChild(label);
        label.appendChild(input);
        label.appendChild(text);

        document.getElementById("amenitieList").appendChild(li);

        // gather checkboxes and add watchers
        const checkboxes = Array.from(document.getElementById("amenitieList").querySelectorAll("input[type='checkbox']"));
        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                // update checkbox counter is selected/unselected
                if (checkboxes.some(checkbox => checkbox.checked)) {
                    document.getElementById("filterByAmenitiesTitle").innerHTML = checkboxes.filter(checkbox => checkbox.checked).length+" Selected Amenities";
                } else {
                    document.getElementById("filterByAmenitiesTitle").innerHTML = "Filter By Amenities";
                }
            });
        });
    });

    // close preloader
    document.getElementById("preloader").style.display = "none";
}