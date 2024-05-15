// ajax requests 

// generic request function
export async function request(endpoint, params = undefined){
    let res;

    try{
        let responseObj = await fetch(endpoint);

        // handle network/request errors gracefully
        if (!responseObj.ok) {
            console.error('A network issue has occured, please try again later.');
            return;
        }

        // get response from request
        res = await responseObj.json();
    } catch (error) {
        // generic graceful fail for 500s
        console.error("Request error (500):", error);
    }

    return res;
}

