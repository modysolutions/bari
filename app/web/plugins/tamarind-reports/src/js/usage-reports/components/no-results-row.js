const NoResultsRow = (colspan = 10, message = '') => {
    return `
<tr>
    <td colspan="${colspan}">${message}</td>
</tr>    
`;
}

export default NoResultsRow;