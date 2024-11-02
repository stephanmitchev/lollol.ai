 
import Moment from 'moment';

const TABLE_HEAD = ["Run ID", "Vendor", "SKU", "Previous Status", "Current Status", "Last Checked"];
 
 
export default function TableWithStripedRows({tableRows, ...props}) {
  Moment.locale('en');
  
  return (
    
      <table className="w-full table-auto text-left">
        <thead>
          <tr>
            {TABLE_HEAD.map((head) => (
              <th key={head} className="border-b border-blue-gray-100 bg-blue-gray-50 p-4">
                  {head}
              </th>
            ))}
          </tr>
        </thead>
        <tbody>
          {tableRows.map(({ run, vendor, sku, previous_status, current_status, created_at }, index) => (
            <tr key={run + '_' + sku} className="even:bg-blue-gray-50/50">
              <td className="p-2">
                  {run} 
              </td>
              <td className="p-2">
                  {vendor}
              </td>
              <td className="p-2">
                  {sku}
              </td>
              <td className="p-2">
                  {previous_status}
              </td>
              <td className="p-2">
                  {current_status}
              </td>
              <td className="p-2">
                {Moment(created_at).format('MMM D, YYYY')}
              </td>
              
             
            </tr>
          ))}
        </tbody>
      </table>
  );
}