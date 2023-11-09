import axios from 'axios';
import '../scss/sync-my-routes.scss';

// https://xkln.net/blog/retrieving-paginated-api-data-with-axios/
ExecuteRequest('/fetch-routes-from-strava').then(() => {
    document.getElementById('fetching-ellipsis').remove();
    document.getElementById('fetching-text').innerHTML = 'All done!';
    window.location.href = '/manage-my-routes';
}).catch(function (error) {
    console.log(error.toString());
    document.getElementById('wait').remove();
    document.getElementById('fetching-ellipsis').remove();
    document.getElementById('spinner').remove();
    document.getElementById('fetching-text').innerHTML = 'Error fetching routes :(';
    document.getElementById('fetched_routes').innerHTML = '<a href="javascript:history.go(-1)">Go back</a>';
});

let totalRoutesFetched = 0;

async function ExecuteRequest(url, page = 1) {
    await axios.post(url, {'page': page}).then(response => {
        totalRoutesFetched += response.data.routesFetched;

        document.getElementById('fetched_routes_count').innerHTML = totalRoutesFetched;

        // Strava API V3 Documentation says that "in certain cases, the number of items returned in the response
        // may be lower than the requested page size, even when that page is not the last. If you need to fully go
        // through the full set of results, prefer iterating until an empty page is returned."
        // See https://developers.strava.com/docs/#pagination
        if (response.data.routesFetched < 50) {
            document.getElementById('fetching-text').innerHTML = 'Finishing';
        }
        if (response.data.routesFetched > 0) {
            return ExecuteRequest(url, ++page);
        }
    });
}