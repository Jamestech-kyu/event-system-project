Networking

Explain how load balancing would work for high-traffic events.

Answer: When a popular event goes live, a single server might crash under the weight of thousands of concurrent registration requests. To prevent this, we use a Load Balancer (like Nginx or HAProxy) as a "traffic cop" sitting in front of our web servers. It distributes incoming HTTP requests across a cluster of multiple backend servers using a Round Robin or Least Connections algorithm. This ensures no single server is overwhelmed, maintaining high availability and fast response times for attendees.
  Database: Transaction Handling & Inventory Logic
 Explain ticket inventory management and database transaction implementation
 Answer: To prevent "overselling" (where two people buy the last ticket at the same millisecond), we cannot rely on simple PHP if statements. Instead, we use Database Transactions (BEGIN...COMMIT)
 The system first places a row-level lock on the specific ticket_type.it checks if the available_quantity is greater than zero.If true, it decrements the count and inserts the registration.If the check fails or a crash occurs, the transaction rolls back, ensuring the database remains consistent and we never sell more tickets than the venue capacity.

 Advanced CGI
 
  Handling Concurrent Requests
  
   Discuss how CGI would handle concurrent requests.
   
   Answer: The main challenge with the Common Gateway Interface (CGI) is that it follows a "fork-and-execute" model. For every single ticket registration request, the server must spawn a brand-new process to run the script. In a high-traffic event, this creates massive overhead on the CPU and RAM, potentially leading to a "denial of service" feeling. Modern environments usually replace basic CGI with FastCGI, which keeps persistent processes alive to handle multiple requests without the constant startup cost.
   
Term Report

Flash Sales & QRCodes

Handling flash sales and QR code possibilities

Answer: * Flash Sales: To survive a "flash sale," we would implement a Virtual Waiting Room (Queue system) and use Database Caching (like Redis) for event details so we don't hit the main MySQL database for every page refresh.QR Codes: For the physical event, we can generate a unique hash for each registration_id and encode it into a QR Code sent via email. At the venue, staff can use a mobile app to scan the code, which instantly updates the attended status in our database, preventing ticket duplication or fraud.
    
