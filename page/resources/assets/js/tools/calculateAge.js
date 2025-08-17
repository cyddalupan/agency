/*
 * Converts 'Date in timestamp' to Age.
 * Works With date_to_timestamp tool
 */
function calculateAge(birthday_timestamp) { // birthday is a date
    var ageDifMs = Date.now() - birthday_timestamp;
    var ageDate = new Date(ageDifMs); // miliseconds from epoch
    return Math.abs(ageDate.getUTCFullYear() - 1970);
}