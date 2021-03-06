<?php $root=""; ?>
<?php require($root."navigation.php"); ?>
<html>
<head>
  <?php load_style($root); ?>
</head>
 
<body>
 
<?php make_navigation("vector_fe_ex1",$root)?>
 
<div class="content">
<a name="comments"></a> 
<div class = "comment">
<h1>Vector Finite Element Example 1 - Solving an uncoupled Poisson Problem</h1>

<br><br>This is the first vector FE example program.  It builds on
the introduction_ex3 example program by showing how to solve a simple
uncoupled Poisson system using vector Lagrange elements.


<br><br>C++ include files that we need
</div>

<div class ="fragment">
<pre>
        #include &lt;iostream&gt;
        #include &lt;algorithm&gt;
        #include &lt;math.h&gt;
        
</pre>
</div>
<div class = "comment">
Basic include files needed for the mesh functionality.
</div>

<div class ="fragment">
<pre>
        #include "libmesh.h"
        #include "mesh.h"
        #include "mesh_generation.h"
        #include "linear_implicit_system.h"
        #include "equation_systems.h"
        #include "exodusII_io.h"
        #include "gmv_io.h"
        
</pre>
</div>
<div class = "comment">
Define the Finite Element object.
</div>

<div class ="fragment">
<pre>
        #include "fe.h"
        
</pre>
</div>
<div class = "comment">
Define Gauss quadrature rules.
</div>

<div class ="fragment">
<pre>
        #include "quadrature_gauss.h"
        
</pre>
</div>
<div class = "comment">
Define useful datatypes for finite element
matrix and vector components.
</div>

<div class ="fragment">
<pre>
        #include "sparse_matrix.h"
        #include "numeric_vector.h"
        #include "dense_matrix.h"
        #include "dense_vector.h"
        #include "elem.h"
        
</pre>
</div>
<div class = "comment">
Define the DofMap, which handles degree of freedom
indexing.
</div>

<div class ="fragment">
<pre>
        #include "dof_map.h"
        
</pre>
</div>
<div class = "comment">
Bring in everything from the libMesh namespace
</div>

<div class ="fragment">
<pre>
        using namespace libMesh;
        
</pre>
</div>
<div class = "comment">
Function prototype.  This is the function that will assemble
the linear system for our Poisson problem.  Note that the
function will take the  EquationSystems object and the
name of the system we are assembling as input.  From the
EquationSystems object we have access to the  Mesh and
other objects we might need.
</div>

<div class ="fragment">
<pre>
        void assemble_poisson(EquationSystems& es,
                              const std::string& system_name);
        
</pre>
</div>
<div class = "comment">
Function prototype for the exact solution.
</div>

<div class ="fragment">
<pre>
        Real exact_solution (const int component,
        		     const Real x,
                             const Real y,
                             const Real z = 0.);
        
        int main (int argc, char** argv)
        {
</pre>
</div>
<div class = "comment">
Initialize libraries.
</div>

<div class ="fragment">
<pre>
          LibMeshInit init (argc, argv);
        
</pre>
</div>
<div class = "comment">
Brief message to the user regarding the program name
and command line arguments.
</div>

<div class ="fragment">
<pre>
          std::cout &lt;&lt; "Running " &lt;&lt; argv[0];
          
          for (int i=1; i&lt;argc; i++)
            std::cout &lt;&lt; " " &lt;&lt; argv[i];
          
          std::cout &lt;&lt; std::endl &lt;&lt; std::endl;
          
</pre>
</div>
<div class = "comment">
Skip this 2D example if libMesh was compiled as 1D-only.
</div>

<div class ="fragment">
<pre>
          libmesh_example_assert(2 &lt;= LIBMESH_DIM, "2D support");
          Mesh mesh;
          
          
</pre>
</div>
<div class = "comment">
Use the MeshTools::Generation mesh generator to create a uniform
2D grid on the square [-1,1]^2.  We instruct the mesh generator
to build a mesh of 15x15 QUAD9 elements.
</div>

<div class ="fragment">
<pre>
          MeshTools::Generation::build_square (mesh, 
                                               15, 15,
                                               -1., 1.,
                                               -1., 1.,
                                               QUAD9);
        
</pre>
</div>
<div class = "comment">
Print information about the mesh to the screen.
</div>

<div class ="fragment">
<pre>
          mesh.print_info();
          
</pre>
</div>
<div class = "comment">
Create an equation systems object.
</div>

<div class ="fragment">
<pre>
          EquationSystems equation_systems (mesh);
          
</pre>
</div>
<div class = "comment">
Declare the Poisson system and its variables.
The Poisson system is another example of a steady system.
</div>

<div class ="fragment">
<pre>
          equation_systems.add_system&lt;LinearImplicitSystem&gt; ("Poisson");
        
</pre>
</div>
<div class = "comment">
Adds the variable "u" to "Poisson".  "u"
will be approximated using second-order approximation
using vector Lagrange elements. Since the mesh is 2-D, "u" will
have two components.
</div>

<div class ="fragment">
<pre>
          equation_systems.get_system("Poisson").add_variable("u", SECOND, LAGRANGE_VEC);
        
</pre>
</div>
<div class = "comment">
Give the system a pointer to the matrix assembly
function.  This will be called when needed by the
library.
</div>

<div class ="fragment">
<pre>
          equation_systems.get_system("Poisson").attach_assemble_function (assemble_poisson);
          
</pre>
</div>
<div class = "comment">
Initialize the data structures for the equation system.
</div>

<div class ="fragment">
<pre>
          equation_systems.init();
          
</pre>
</div>
<div class = "comment">
Prints information about the system to the screen.
</div>

<div class ="fragment">
<pre>
          equation_systems.print_info();
        
</pre>
</div>
<div class = "comment">
Solve the system "Poisson".  Note that calling this
member will assemble the linear system and invoke
the default numerical solver.  With PETSc the solver can be
controlled from the command line.  For example,
you can invoke conjugate gradient with:

<br><br>./vector_fe_ex1 -ksp_type cg

<br><br>You can also get a nice X-window that monitors the solver
convergence with:

<br><br>./vector_fe_ex1 -ksp_xmonitor

<br><br>if you linked against the appropriate X libraries when you
built PETSc.
</div>

<div class ="fragment">
<pre>
          equation_systems.get_system("Poisson").solve();
        
        #ifdef LIBMESH_HAVE_EXODUS_API
          ExodusII_IO(mesh).write_equation_systems( "out.e", equation_systems);
        #endif
        
        #ifdef LIBMESH_HAVE_GMV
          GMVIO(mesh).write_equation_systems( "out.gmv", equation_systems);
        #endif
        
</pre>
</div>
<div class = "comment">
All done.  
</div>

<div class ="fragment">
<pre>
          return 0;
        }
        
        
        
</pre>
</div>
<div class = "comment">
We now define the matrix assembly function for the
Poisson system.  We need to first compute element
matrices and right-hand sides, and then take into
account the boundary conditions, which will be handled
via a penalty method.
</div>

<div class ="fragment">
<pre>
        void assemble_poisson(EquationSystems& es,
                              const std::string& system_name)
        {
          
</pre>
</div>
<div class = "comment">
It is a good idea to make sure we are assembling
the proper system.
</div>

<div class ="fragment">
<pre>
          libmesh_assert (system_name == "Poisson");
        
          
</pre>
</div>
<div class = "comment">
Get a constant reference to the mesh object.
</div>

<div class ="fragment">
<pre>
          const MeshBase& mesh = es.get_mesh();
        
</pre>
</div>
<div class = "comment">
The dimension that we are running
</div>

<div class ="fragment">
<pre>
          const unsigned int dim = mesh.mesh_dimension();
        
</pre>
</div>
<div class = "comment">
Get a reference to the LinearImplicitSystem we are solving
</div>

<div class ="fragment">
<pre>
          LinearImplicitSystem& system = es.get_system&lt;LinearImplicitSystem&gt; ("Poisson");
        
</pre>
</div>
<div class = "comment">
A reference to the  DofMap object for this system.  The  DofMap
object handles the index translation from node and element numbers
to degree of freedom numbers.  We will talk more about the  DofMap
in future examples.
</div>

<div class ="fragment">
<pre>
          const DofMap& dof_map = system.get_dof_map();
          
</pre>
</div>
<div class = "comment">
Get a constant reference to the Finite Element type
for the first (and only) variable in the system.
</div>

<div class ="fragment">
<pre>
          FEType fe_type = dof_map.variable_type(0);
          
</pre>
</div>
<div class = "comment">
Build a Finite Element object of the specified type.
Note that FEVectorBase is a typedef for the templated FE
class.
</div>

<div class ="fragment">
<pre>
          AutoPtr&lt;FEVectorBase&gt; fe (FEVectorBase::build(dim, fe_type));
          
</pre>
</div>
<div class = "comment">
A 5th order Gauss quadrature rule for numerical integration.
</div>

<div class ="fragment">
<pre>
          QGauss qrule (dim, FIFTH);
          
</pre>
</div>
<div class = "comment">
Tell the finite element object to use our quadrature rule.
</div>

<div class ="fragment">
<pre>
          fe-&gt;attach_quadrature_rule (&qrule);
          
</pre>
</div>
<div class = "comment">
Declare a special finite element object for
boundary integration.
</div>

<div class ="fragment">
<pre>
          AutoPtr&lt;FEVectorBase&gt; fe_face (FEVectorBase::build(dim, fe_type));
          
</pre>
</div>
<div class = "comment">
Boundary integration requires one quadraure rule,
with dimensionality one less than the dimensionality
of the element.
</div>

<div class ="fragment">
<pre>
          QGauss qface(dim-1, FIFTH);
          
</pre>
</div>
<div class = "comment">
Tell the finite element object to use our
quadrature rule.
</div>

<div class ="fragment">
<pre>
          fe_face-&gt;attach_quadrature_rule (&qface);
        
</pre>
</div>
<div class = "comment">
Here we define some references to cell-specific data that
will be used to assemble the linear system.

<br><br>The element Jacobian * quadrature weight at each integration point.   
</div>

<div class ="fragment">
<pre>
          const std::vector&lt;Real&gt;& JxW = fe-&gt;get_JxW();
        
</pre>
</div>
<div class = "comment">
The physical XY locations of the quadrature points on the element.
These might be useful for evaluating spatially varying material
properties at the quadrature points.
</div>

<div class ="fragment">
<pre>
          const std::vector&lt;Point&gt;& q_point = fe-&gt;get_xyz();
        
</pre>
</div>
<div class = "comment">
The element shape functions evaluated at the quadrature points.
Notice the shape functions are a vector rather than a scalar.
</div>

<div class ="fragment">
<pre>
          const std::vector&lt;std::vector&lt;RealGradient&gt; &gt;& phi = fe-&gt;get_phi();
        
</pre>
</div>
<div class = "comment">
The element shape function gradients evaluated at the quadrature
points. Notice that the shape function gradients are a tensor.
</div>

<div class ="fragment">
<pre>
          const std::vector&lt;std::vector&lt;RealTensor&gt; &gt;& dphi = fe-&gt;get_dphi();
        
</pre>
</div>
<div class = "comment">
Define data structures to contain the element matrix
and right-hand-side vector contribution.  Following
basic finite element terminology we will denote these
"Ke" and "Fe".  These datatypes are templated on
Number, which allows the same code to work for real
or complex numbers.
</div>

<div class ="fragment">
<pre>
          DenseMatrix&lt;Number&gt; Ke;
          DenseVector&lt;Number&gt; Fe;
        
        
</pre>
</div>
<div class = "comment">
This vector will hold the degree of freedom indices for
the element.  These define where in the global system
the element degrees of freedom get mapped.
</div>

<div class ="fragment">
<pre>
          std::vector&lt;unsigned int&gt; dof_indices;
        
</pre>
</div>
<div class = "comment">
Now we will loop over all the elements in the mesh.
We will compute the element matrix and right-hand-side
contribution.

<br><br>Element iterators are a nice way to iterate through all the
elements, or all the elements that have some property.  The
iterator el will iterate from the first to the last element on
the local processor.  The iterator end_el tells us when to stop.
It is smart to make this one const so that we don't accidentally
mess it up!  In case users later modify this program to include
refinement, we will be safe and will only consider the active
elements; hence we use a variant of the \p active_elem_iterator.
</div>

<div class ="fragment">
<pre>
          MeshBase::const_element_iterator       el     = mesh.active_local_elements_begin();
          const MeshBase::const_element_iterator end_el = mesh.active_local_elements_end();
         
</pre>
</div>
<div class = "comment">
Loop over the elements.  Note that  ++el is preferred to
el++ since the latter requires an unnecessary temporary
object.
</div>

<div class ="fragment">
<pre>
          for ( ; el != end_el ; ++el)
            {
</pre>
</div>
<div class = "comment">
Store a pointer to the element we are currently
working on.  This allows for nicer syntax later.
</div>

<div class ="fragment">
<pre>
              const Elem* elem = *el;
        
</pre>
</div>
<div class = "comment">
Get the degree of freedom indices for the
current element.  These define where in the global
matrix and right-hand-side this element will
contribute to.
</div>

<div class ="fragment">
<pre>
              dof_map.dof_indices (elem, dof_indices);
        
</pre>
</div>
<div class = "comment">
Compute the element-specific data for the current
element.  This involves computing the location of the
quadrature points (q_point) and the shape functions
(phi, dphi) for the current element.
</div>

<div class ="fragment">
<pre>
              fe-&gt;reinit (elem);
        
        
</pre>
</div>
<div class = "comment">
Zero the element matrix and right-hand side before
summing them.  We use the resize member here because
the number of degrees of freedom might have changed from
the last element.  Note that this will be the case if the
element type is different (i.e. the last element was a
triangle, now we are on a quadrilateral).


<br><br>The  DenseMatrix::resize() and the  DenseVector::resize()
members will automatically zero out the matrix  and vector.
</div>

<div class ="fragment">
<pre>
              Ke.resize (dof_indices.size(),
                         dof_indices.size());
        
              Fe.resize (dof_indices.size());
        
</pre>
</div>
<div class = "comment">
Now loop over the quadrature points.  This handles
the numeric integration.
</div>

<div class ="fragment">
<pre>
              for (unsigned int qp=0; qp&lt;qrule.n_points(); qp++)
                {
        
</pre>
</div>
<div class = "comment">
Now we will build the element matrix.  This involves
a double loop to integrate the test funcions (i) against
the trial functions (j).
</div>

<div class ="fragment">
<pre>
                  for (unsigned int i=0; i&lt;phi.size(); i++)
                    for (unsigned int j=0; j&lt;phi.size(); j++)
                      {
                        Ke(i,j) += JxW[qp]*( dphi[i][qp].contract(dphi[j][qp]) );
                      }
                  
</pre>
</div>
<div class = "comment">
This is the end of the matrix summation loop
Now we build the element right-hand-side contribution.
This involves a single loop in which we integrate the
"forcing function" in the PDE against the test functions.
</div>

<div class ="fragment">
<pre>
                  {
                    const Real x = q_point[qp](0);
                    const Real y = q_point[qp](1);
                    const Real eps = 1.e-3;
                    
        
</pre>
</div>
<div class = "comment">
"f" is the forcing function for the Poisson equation.
In this case we set f to be a finite difference
Laplacian approximation to the (known) exact solution.

<br><br>We will use the second-order accurate FD Laplacian
approximation, which in 2D is

<br><br>u_xx + u_yy = (u(i,j-1) + u(i,j+1) +
u(i-1,j) + u(i+1,j) +
-4*u(i,j))/h^2

<br><br>Since the value of the forcing function depends only
on the location of the quadrature point (q_point[qp])
we will compute it here, outside of the i-loop
</div>

<div class ="fragment">
<pre>
                    const Real fx = -(exact_solution(0,x,y-eps) +
        			      exact_solution(0,x,y+eps) +
        			      exact_solution(0,x-eps,y) +
        			      exact_solution(0,x+eps,y) -
        			      4.*exact_solution(0,x,y))/eps/eps;
        
        	    const Real fy = -(exact_solution(1,x,y-eps) +
        			      exact_solution(1,x,y+eps) +
        			      exact_solution(1,x-eps,y) +
        			      exact_solution(1,x+eps,y) -
        			      4.*exact_solution(1,x,y))/eps/eps;
        
        	    const RealGradient f( fx, fy );
        
                    for (unsigned int i=0; i&lt;phi.size(); i++)
                      Fe(i) += JxW[qp]*f*phi[i][qp];
                  } 
                } 
              
</pre>
</div>
<div class = "comment">
We have now reached the end of the RHS summation,
and the end of quadrature point loop, so
the interior element integration has
been completed.  However, we have not yet addressed
boundary conditions.  For this example we will only
consider simple Dirichlet boundary conditions.

<br><br>There are several ways Dirichlet boundary conditions
can be imposed.  A simple approach, which works for
interpolary bases like the standard Lagrange polynomials,
is to assign function values to the
degrees of freedom living on the domain boundary. This
works well for interpolary bases, but is more difficult
when non-interpolary (e.g Legendre or Hierarchic) bases
are used.

<br><br>Dirichlet boundary conditions can also be imposed with a
"penalty" method.  In this case essentially the L2 projection
of the boundary values are added to the matrix. The
projection is multiplied by some large factor so that, in
floating point arithmetic, the existing (smaller) entries
in the matrix and right-hand-side are effectively ignored.

<br><br>This amounts to adding a term of the form (in latex notation)

<br><br>\frac{1}{\epsilon} \int_{\delta \Omega} \phi_i \phi_j = \frac{1}{\epsilon} \int_{\delta \Omega} u \phi_i

<br><br>where

<br><br>\frac{1}{\epsilon} is the penalty parameter, defined such that \epsilon << 1
</div>

<div class ="fragment">
<pre>
              {
        
</pre>
</div>
<div class = "comment">
The following loop is over the sides of the element.
If the element has no neighbor on a side then that
side MUST live on a boundary of the domain.
</div>

<div class ="fragment">
<pre>
                for (unsigned int side=0; side&lt;elem-&gt;n_sides(); side++)
                  if (elem-&gt;neighbor(side) == NULL)
                    {
</pre>
</div>
<div class = "comment">
The value of the shape functions at the quadrature
points.
</div>

<div class ="fragment">
<pre>
                      const std::vector&lt;std::vector&lt;RealGradient&gt; &gt;&  phi_face = fe_face-&gt;get_phi();
                      
</pre>
</div>
<div class = "comment">
The Jacobian * Quadrature Weight at the quadrature
points on the face.
</div>

<div class ="fragment">
<pre>
                      const std::vector&lt;Real&gt;& JxW_face = fe_face-&gt;get_JxW();
                      
</pre>
</div>
<div class = "comment">
The XYZ locations (in physical space) of the
quadrature points on the face.  This is where
we will interpolate the boundary value function.
</div>

<div class ="fragment">
<pre>
                      const std::vector&lt;Point&gt;& qface_point = fe_face-&gt;get_xyz();
                      
</pre>
</div>
<div class = "comment">
Compute the shape function values on the element
face.
</div>

<div class ="fragment">
<pre>
                      fe_face-&gt;reinit(elem, side);
                      
</pre>
</div>
<div class = "comment">
Loop over the face quadrature points for integration.
</div>

<div class ="fragment">
<pre>
                      for (unsigned int qp=0; qp&lt;qface.n_points(); qp++)
                        {
        
</pre>
</div>
<div class = "comment">
The location on the boundary of the current
face quadrature point.
</div>

<div class ="fragment">
<pre>
                          const Real xf = qface_point[qp](0);
                          const Real yf = qface_point[qp](1);
        
</pre>
</div>
<div class = "comment">
The penalty value.  \frac{1}{\epsilon}
in the discussion above.
</div>

<div class ="fragment">
<pre>
                          const Real penalty = 1.e10;
        
</pre>
</div>
<div class = "comment">
The boundary values.
</div>

<div class ="fragment">
<pre>
                          const RealGradient f( exact_solution(0, xf, yf), 
        					exact_solution(1, xf, yf) );
        		  
</pre>
</div>
<div class = "comment">
Matrix contribution of the L2 projection. 
</div>

<div class ="fragment">
<pre>
                          for (unsigned int i=0; i&lt;phi_face.size(); i++)
                            for (unsigned int j=0; j&lt;phi_face.size(); j++)
                              Ke(i,j) += JxW_face[qp]*penalty*phi_face[i][qp]*phi_face[j][qp];
        
</pre>
</div>
<div class = "comment">
Right-hand-side contribution of the L2
projection.
</div>

<div class ="fragment">
<pre>
                          for (unsigned int i=0; i&lt;phi_face.size(); i++)
                            Fe(i) += JxW_face[qp]*penalty*f*phi_face[i][qp];
                        } 
                    }
              }
              
</pre>
</div>
<div class = "comment">
We have now finished the quadrature point loop,
and have therefore applied all the boundary conditions.


<br><br>If this assembly program were to be used on an adaptive mesh,
we would have to apply any hanging node constraint equations
dof_map.constrain_element_matrix_and_vector (Ke, Fe, dof_indices);


<br><br>The element matrix and right-hand-side are now built
for this element.  Add them to the global matrix and
right-hand-side vector.  The  SparseMatrix::add_matrix()
and  NumericVector::add_vector() members do this for us.
</div>

<div class ="fragment">
<pre>
              system.matrix-&gt;add_matrix (Ke, dof_indices);
              system.rhs-&gt;add_vector    (Fe, dof_indices);
            }
          
</pre>
</div>
<div class = "comment">
All done!
</div>

<div class ="fragment">
<pre>
        }
</pre>
</div>

<a name="nocomments"></a> 
<br><br><br> <h1> The program without comments: </h1> 
<pre> 
  
  #include &lt;iostream&gt;
  #include &lt;algorithm&gt;
  #include &lt;math.h&gt;
  
  #include <B><FONT COLOR="#BC8F8F">&quot;libmesh.h&quot;</FONT></B>
  #include <B><FONT COLOR="#BC8F8F">&quot;mesh.h&quot;</FONT></B>
  #include <B><FONT COLOR="#BC8F8F">&quot;mesh_generation.h&quot;</FONT></B>
  #include <B><FONT COLOR="#BC8F8F">&quot;linear_implicit_system.h&quot;</FONT></B>
  #include <B><FONT COLOR="#BC8F8F">&quot;equation_systems.h&quot;</FONT></B>
  #include <B><FONT COLOR="#BC8F8F">&quot;exodusII_io.h&quot;</FONT></B>
  #include <B><FONT COLOR="#BC8F8F">&quot;gmv_io.h&quot;</FONT></B>
  
  #include <B><FONT COLOR="#BC8F8F">&quot;fe.h&quot;</FONT></B>
  
  #include <B><FONT COLOR="#BC8F8F">&quot;quadrature_gauss.h&quot;</FONT></B>
  
  #include <B><FONT COLOR="#BC8F8F">&quot;sparse_matrix.h&quot;</FONT></B>
  #include <B><FONT COLOR="#BC8F8F">&quot;numeric_vector.h&quot;</FONT></B>
  #include <B><FONT COLOR="#BC8F8F">&quot;dense_matrix.h&quot;</FONT></B>
  #include <B><FONT COLOR="#BC8F8F">&quot;dense_vector.h&quot;</FONT></B>
  #include <B><FONT COLOR="#BC8F8F">&quot;elem.h&quot;</FONT></B>
  
  #include <B><FONT COLOR="#BC8F8F">&quot;dof_map.h&quot;</FONT></B>
  
  using namespace libMesh;
  
  <B><FONT COLOR="#228B22">void</FONT></B> assemble_poisson(EquationSystems&amp; es,
                        <B><FONT COLOR="#228B22">const</FONT></B> std::string&amp; system_name);
  
  Real exact_solution (<B><FONT COLOR="#228B22">const</FONT></B> <B><FONT COLOR="#228B22">int</FONT></B> component,
  		     <B><FONT COLOR="#228B22">const</FONT></B> Real x,
                       <B><FONT COLOR="#228B22">const</FONT></B> Real y,
                       <B><FONT COLOR="#228B22">const</FONT></B> Real z = 0.);
  
  <B><FONT COLOR="#228B22">int</FONT></B> main (<B><FONT COLOR="#228B22">int</FONT></B> argc, <B><FONT COLOR="#228B22">char</FONT></B>** argv)
  {
    LibMeshInit init (argc, argv);
  
    <B><FONT COLOR="#5F9EA0">std</FONT></B>::cout &lt;&lt; <B><FONT COLOR="#BC8F8F">&quot;Running &quot;</FONT></B> &lt;&lt; argv[0];
    
    <B><FONT COLOR="#A020F0">for</FONT></B> (<B><FONT COLOR="#228B22">int</FONT></B> i=1; i&lt;argc; i++)
      <B><FONT COLOR="#5F9EA0">std</FONT></B>::cout &lt;&lt; <B><FONT COLOR="#BC8F8F">&quot; &quot;</FONT></B> &lt;&lt; argv[i];
    
    <B><FONT COLOR="#5F9EA0">std</FONT></B>::cout &lt;&lt; std::endl &lt;&lt; std::endl;
    
    libmesh_example_assert(2 &lt;= LIBMESH_DIM, <B><FONT COLOR="#BC8F8F">&quot;2D support&quot;</FONT></B>);
    Mesh mesh;
    
    
    <B><FONT COLOR="#5F9EA0">MeshTools</FONT></B>::Generation::build_square (mesh, 
                                         15, 15,
                                         -1., 1.,
                                         -1., 1.,
                                         QUAD9);
  
    mesh.print_info();
    
    EquationSystems equation_systems (mesh);
    
    equation_systems.add_system&lt;LinearImplicitSystem&gt; (<B><FONT COLOR="#BC8F8F">&quot;Poisson&quot;</FONT></B>);
  
    equation_systems.get_system(<B><FONT COLOR="#BC8F8F">&quot;Poisson&quot;</FONT></B>).add_variable(<B><FONT COLOR="#BC8F8F">&quot;u&quot;</FONT></B>, SECOND, LAGRANGE_VEC);
  
    equation_systems.get_system(<B><FONT COLOR="#BC8F8F">&quot;Poisson&quot;</FONT></B>).attach_assemble_function (assemble_poisson);
    
    equation_systems.init();
    
    equation_systems.print_info();
  
    equation_systems.get_system(<B><FONT COLOR="#BC8F8F">&quot;Poisson&quot;</FONT></B>).solve();
  
  #ifdef LIBMESH_HAVE_EXODUS_API
    ExodusII_IO(mesh).write_equation_systems( <B><FONT COLOR="#BC8F8F">&quot;out.e&quot;</FONT></B>, equation_systems);
  #endif
  
  #ifdef LIBMESH_HAVE_GMV
    GMVIO(mesh).write_equation_systems( <B><FONT COLOR="#BC8F8F">&quot;out.gmv&quot;</FONT></B>, equation_systems);
  #endif
  
    <B><FONT COLOR="#A020F0">return</FONT></B> 0;
  }
  
  
  
  <B><FONT COLOR="#228B22">void</FONT></B> assemble_poisson(EquationSystems&amp; es,
                        <B><FONT COLOR="#228B22">const</FONT></B> std::string&amp; system_name)
  {
    
    libmesh_assert (system_name == <B><FONT COLOR="#BC8F8F">&quot;Poisson&quot;</FONT></B>);
  
    
    <B><FONT COLOR="#228B22">const</FONT></B> MeshBase&amp; mesh = es.get_mesh();
  
    <B><FONT COLOR="#228B22">const</FONT></B> <B><FONT COLOR="#228B22">unsigned</FONT></B> <B><FONT COLOR="#228B22">int</FONT></B> dim = mesh.mesh_dimension();
  
    LinearImplicitSystem&amp; system = es.get_system&lt;LinearImplicitSystem&gt; (<B><FONT COLOR="#BC8F8F">&quot;Poisson&quot;</FONT></B>);
  
    <B><FONT COLOR="#228B22">const</FONT></B> DofMap&amp; dof_map = system.get_dof_map();
    
    FEType fe_type = dof_map.variable_type(0);
    
    AutoPtr&lt;FEVectorBase&gt; fe (FEVectorBase::build(dim, fe_type));
    
    QGauss qrule (dim, FIFTH);
    
    fe-&gt;attach_quadrature_rule (&amp;qrule);
    
    AutoPtr&lt;FEVectorBase&gt; fe_face (FEVectorBase::build(dim, fe_type));
    
    QGauss qface(dim-1, FIFTH);
    
    fe_face-&gt;attach_quadrature_rule (&amp;qface);
  
    <B><FONT COLOR="#228B22">const</FONT></B> std::vector&lt;Real&gt;&amp; JxW = fe-&gt;get_JxW();
  
    <B><FONT COLOR="#228B22">const</FONT></B> std::vector&lt;Point&gt;&amp; q_point = fe-&gt;get_xyz();
  
    <B><FONT COLOR="#228B22">const</FONT></B> std::vector&lt;std::vector&lt;RealGradient&gt; &gt;&amp; phi = fe-&gt;get_phi();
  
    <B><FONT COLOR="#228B22">const</FONT></B> std::vector&lt;std::vector&lt;RealTensor&gt; &gt;&amp; dphi = fe-&gt;get_dphi();
  
    DenseMatrix&lt;Number&gt; Ke;
    DenseVector&lt;Number&gt; Fe;
  
  
    <B><FONT COLOR="#5F9EA0">std</FONT></B>::vector&lt;<B><FONT COLOR="#228B22">unsigned</FONT></B> <B><FONT COLOR="#228B22">int</FONT></B>&gt; dof_indices;
  
    <B><FONT COLOR="#5F9EA0">MeshBase</FONT></B>::const_element_iterator       el     = mesh.active_local_elements_begin();
    <B><FONT COLOR="#228B22">const</FONT></B> MeshBase::const_element_iterator end_el = mesh.active_local_elements_end();
   
    <B><FONT COLOR="#A020F0">for</FONT></B> ( ; el != end_el ; ++el)
      {
        <B><FONT COLOR="#228B22">const</FONT></B> Elem* elem = *el;
  
        dof_map.dof_indices (elem, dof_indices);
  
        fe-&gt;reinit (elem);
  
  
  
        Ke.resize (dof_indices.size(),
                   dof_indices.size());
  
        Fe.resize (dof_indices.size());
  
        <B><FONT COLOR="#A020F0">for</FONT></B> (<B><FONT COLOR="#228B22">unsigned</FONT></B> <B><FONT COLOR="#228B22">int</FONT></B> qp=0; qp&lt;qrule.n_points(); qp++)
          {
  
            <B><FONT COLOR="#A020F0">for</FONT></B> (<B><FONT COLOR="#228B22">unsigned</FONT></B> <B><FONT COLOR="#228B22">int</FONT></B> i=0; i&lt;phi.size(); i++)
              <B><FONT COLOR="#A020F0">for</FONT></B> (<B><FONT COLOR="#228B22">unsigned</FONT></B> <B><FONT COLOR="#228B22">int</FONT></B> j=0; j&lt;phi.size(); j++)
                {
                  Ke(i,j) += JxW[qp]*( dphi[i][qp].contract(dphi[j][qp]) );
                }
            
            {
              <B><FONT COLOR="#228B22">const</FONT></B> Real x = q_point[qp](0);
              <B><FONT COLOR="#228B22">const</FONT></B> Real y = q_point[qp](1);
              <B><FONT COLOR="#228B22">const</FONT></B> Real eps = 1.e-3;
              
  
              <B><FONT COLOR="#228B22">const</FONT></B> Real fx = -(exact_solution(0,x,y-eps) +
  			      exact_solution(0,x,y+eps) +
  			      exact_solution(0,x-eps,y) +
  			      exact_solution(0,x+eps,y) -
  			      4.*exact_solution(0,x,y))/eps/eps;
  
  	    <B><FONT COLOR="#228B22">const</FONT></B> Real fy = -(exact_solution(1,x,y-eps) +
  			      exact_solution(1,x,y+eps) +
  			      exact_solution(1,x-eps,y) +
  			      exact_solution(1,x+eps,y) -
  			      4.*exact_solution(1,x,y))/eps/eps;
  
  	    <B><FONT COLOR="#228B22">const</FONT></B> RealGradient f( fx, fy );
  
              <B><FONT COLOR="#A020F0">for</FONT></B> (<B><FONT COLOR="#228B22">unsigned</FONT></B> <B><FONT COLOR="#228B22">int</FONT></B> i=0; i&lt;phi.size(); i++)
                Fe(i) += JxW[qp]*f*phi[i][qp];
            } 
          } 
        
        {
  
          <B><FONT COLOR="#A020F0">for</FONT></B> (<B><FONT COLOR="#228B22">unsigned</FONT></B> <B><FONT COLOR="#228B22">int</FONT></B> side=0; side&lt;elem-&gt;n_sides(); side++)
            <B><FONT COLOR="#A020F0">if</FONT></B> (elem-&gt;neighbor(side) == NULL)
              {
                <B><FONT COLOR="#228B22">const</FONT></B> std::vector&lt;std::vector&lt;RealGradient&gt; &gt;&amp;  phi_face = fe_face-&gt;get_phi();
                
                <B><FONT COLOR="#228B22">const</FONT></B> std::vector&lt;Real&gt;&amp; JxW_face = fe_face-&gt;get_JxW();
                
                <B><FONT COLOR="#228B22">const</FONT></B> std::vector&lt;Point&gt;&amp; qface_point = fe_face-&gt;get_xyz();
                
                fe_face-&gt;reinit(elem, side);
                
                <B><FONT COLOR="#A020F0">for</FONT></B> (<B><FONT COLOR="#228B22">unsigned</FONT></B> <B><FONT COLOR="#228B22">int</FONT></B> qp=0; qp&lt;qface.n_points(); qp++)
                  {
  
                    <B><FONT COLOR="#228B22">const</FONT></B> Real xf = qface_point[qp](0);
                    <B><FONT COLOR="#228B22">const</FONT></B> Real yf = qface_point[qp](1);
  
                    <B><FONT COLOR="#228B22">const</FONT></B> Real penalty = 1.e10;
  
  		  <B><FONT COLOR="#228B22">const</FONT></B> RealGradient f( exact_solution(0, xf, yf), 
  					exact_solution(1, xf, yf) );
  		  
                    <B><FONT COLOR="#A020F0">for</FONT></B> (<B><FONT COLOR="#228B22">unsigned</FONT></B> <B><FONT COLOR="#228B22">int</FONT></B> i=0; i&lt;phi_face.size(); i++)
                      <B><FONT COLOR="#A020F0">for</FONT></B> (<B><FONT COLOR="#228B22">unsigned</FONT></B> <B><FONT COLOR="#228B22">int</FONT></B> j=0; j&lt;phi_face.size(); j++)
                        Ke(i,j) += JxW_face[qp]*penalty*phi_face[i][qp]*phi_face[j][qp];
  
                    <B><FONT COLOR="#A020F0">for</FONT></B> (<B><FONT COLOR="#228B22">unsigned</FONT></B> <B><FONT COLOR="#228B22">int</FONT></B> i=0; i&lt;phi_face.size(); i++)
                      Fe(i) += JxW_face[qp]*penalty*f*phi_face[i][qp];
                  } 
              }
        }
        
  
  
        system.matrix-&gt;add_matrix (Ke, dof_indices);
        system.rhs-&gt;add_vector    (Fe, dof_indices);
      }
    
  }
</pre> 
<a name="output"></a> 
<br><br><br> <h1> The console output of the program: </h1> 
<pre>
Linking vector_fe_ex1-opt...
***************************************************************
* Running Example  mpirun -np 6 ./vector_fe_ex1-opt -pc_type bjacobi -sub_pc_type ilu -sub_pc_factor_levels 4 -sub_pc_factor_zeropivot 0 -ksp_right_pc -log_summary
***************************************************************
 
Running ./vector_fe_ex1-opt -pc_type bjacobi -sub_pc_type ilu -sub_pc_factor_levels 4 -sub_pc_factor_zeropivot 0 -ksp_right_pc -log_summary

 Mesh Information:
  mesh_dimension()=2
  spatial_dimension()=3
  n_nodes()=961
    n_local_nodes()=175
  n_elem()=225
    n_local_elem()=37
    n_active_elem()=225
  n_subdomains()=1
  n_partitions()=6
  n_processors()=6
  n_threads()=1
  processor_id()=0

 EquationSystems
  n_systems()=1
   System #0, "Poisson"
    Type "LinearImplicit"
    Variables="u" 
    Finite Element Types="LAGRANGE_VEC", "JACOBI_20_00" 
    Infinite Element Mapping="CARTESIAN" 
    Approximation Orders="SECOND", "THIRD" 
    n_dofs()=1922
    n_local_dofs()=350
    n_constrained_dofs()=0
    n_local_constrained_dofs()=0
    n_vectors()=1
    n_matrices()=1
    DofMap Sparsity
      Average  On-Processor Bandwidth <= 28.3543
      Average Off-Processor Bandwidth <= 2.56
      Maximum  On-Processor Bandwidth <= 50
      Maximum Off-Processor Bandwidth <= 32
    DofMap Constraints
      Number of DoF Constraints = 0
      Number of Node Constraints = 0

************************************************************************************************************************
***             WIDEN YOUR WINDOW TO 120 CHARACTERS.  Use 'enscript -r -fCourier9' to print this document            ***
************************************************************************************************************************

---------------------------------------------- PETSc Performance Summary: ----------------------------------------------

./vector_fe_ex1-opt on a intel-11. named daedalus with 6 processors, by roystgnr Fri Aug 24 15:28:17 2012
Using Petsc Release Version 3.1.0, Patch 5, Mon Sep 27 11:51:54 CDT 2010

                         Max       Max/Min        Avg      Total 
Time (sec):           7.337e-02      1.05160   7.042e-02
Objects:              5.300e+01      1.00000   5.300e+01
Flops:                4.231e+06      1.62377   3.568e+06  2.141e+07
Flops/sec:            6.062e+07      1.62322   5.064e+07  3.038e+08
MPI Messages:         1.225e+02      2.50000   7.400e+01  4.440e+02
MPI Message Lengths:  4.379e+04      1.64513   4.557e+02  2.023e+05
MPI Reductions:       8.400e+01      1.00000

Flop counting convention: 1 flop = 1 real number operation of type (multiply/divide/add/subtract)
                            e.g., VecAXPY() for real vectors of length N --> 2N flops
                            and VecAXPY() for complex vectors of length N --> 8N flops

Summary of Stages:   ----- Time ------  ----- Flops -----  --- Messages ---  -- Message Lengths --  -- Reductions --
                        Avg     %Total     Avg     %Total   counts   %Total     Avg         %Total   counts   %Total 
 0:      Main Stage: 7.0351e-02  99.9%  2.1410e+07 100.0%  4.440e+02 100.0%  4.557e+02      100.0%  6.800e+01  81.0% 

------------------------------------------------------------------------------------------------------------------------
See the 'Profiling' chapter of the users' manual for details on interpreting output.
Phase summary info:
   Count: number of times phase was executed
   Time and Flops: Max - maximum over all processors
                   Ratio - ratio of maximum to minimum over all processors
   Mess: number of messages sent
   Avg. len: average message length
   Reduct: number of global reductions
   Global: entire computation
   Stage: stages of a computation. Set stages with PetscLogStagePush() and PetscLogStagePop().
      %T - percent time in this phase         %F - percent flops in this phase
      %M - percent messages in this phase     %L - percent message lengths in this phase
      %R - percent reductions in this phase
   Total Mflop/s: 10e-6 * (sum of flops over all processors)/(max time over all processors)
------------------------------------------------------------------------------------------------------------------------
Event                Count      Time (sec)     Flops                             --- Global ---  --- Stage ---   Total
                   Max Ratio  Max     Ratio   Max  Ratio  Mess   Avg len Reduct  %T %F %M %L %R  %T %F %M %L %R Mflop/s
------------------------------------------------------------------------------------------------------------------------

--- Event Stage 0: Main Stage

VecMDot               16 1.0 4.5462e-03 6.3 9.72e+04 1.3 0.0e+00 0.0e+00 1.6e+01  4  2  0  0 19   4  2  0  0 24   115
VecNorm               18 1.0 1.4606e-0220.6 1.29e+04 1.3 0.0e+00 0.0e+00 1.8e+01 13  0  0  0 21  13  0  0  0 26     5
VecScale              17 1.0 4.7207e-05 2.4 6.09e+03 1.3 0.0e+00 0.0e+00 0.0e+00  0  0  0  0  0   0  0  0  0  0   692
VecCopy                4 1.0 5.9605e-06 1.5 0.00e+00 0.0 0.0e+00 0.0e+00 0.0e+00  0  0  0  0  0   0  0  0  0  0     0
VecSet                22 1.0 1.5187e-0417.2 0.00e+00 0.0 0.0e+00 0.0e+00 0.0e+00  0  0  0  0  0   0  0  0  0  0     0
VecAXPY                2 1.0 1.3032e-02 2.4 1.43e+03 1.3 0.0e+00 0.0e+00 0.0e+00 10  0  0  0  0  10  0  0  0  0     1
VecMAXPY              17 1.0 5.2214e-05 1.6 1.09e+05 1.3 0.0e+00 0.0e+00 0.0e+00  0  3  0  0  0   0  3  0  0  0 11190
VecAssemblyBegin       3 1.0 6.0678e-04 3.3 0.00e+00 0.0 1.8e+01 2.5e+02 9.0e+00  1  0  4  2 11   1  0  4  2 13     0
VecAssemblyEnd         3 1.0 2.5034e-05 1.2 0.00e+00 0.0 0.0e+00 0.0e+00 0.0e+00  0  0  0  0  0   0  0  0  0  0     0
VecScatterBegin       18 1.0 1.0300e-04 1.7 0.00e+00 0.0 3.2e+02 3.1e+02 0.0e+00  0  0 73 50  0   0  0 73 50  0     0
VecScatterEnd         18 1.0 1.3031e-02269.2 0.00e+00 0.0 0.0e+00 0.0e+00 0.0e+00  8  0  0  0  0   8  0  0  0  0     0
VecNormalize          17 1.0 1.4640e-0219.4 1.83e+04 1.3 0.0e+00 0.0e+00 1.7e+01 13  0  0  0 20  13  0  0  0 25     7
MatMult               17 1.0 1.3247e-0227.7 3.73e+05 1.4 3.1e+02 3.0e+02 0.0e+00  9  9 69 46  0   9  9 69 46  0   148
MatSolve              17 1.0 2.1708e-03 4.7 1.30e+06 1.5 0.0e+00 0.0e+00 0.0e+00  1 32  0  0  0   1 32  0  0  0  3118
MatLUFactorNum         1 1.0 1.4839e-03 1.7 2.33e+06 1.8 0.0e+00 0.0e+00 0.0e+00  2 54  0  0  0   2 54  0  0  0  7727
MatILUFactorSym        1 1.0 1.2148e-02 4.1 0.00e+00 0.0 0.0e+00 0.0e+00 1.0e+00  8  0  0  0  1   8  0  0  0  1     0
MatAssemblyBegin       2 1.0 3.1059e-0313.5 0.00e+00 0.0 2.7e+01 3.3e+03 4.0e+00  4  0  6 44  5   4  0  6 44  6     0
MatAssemblyEnd         2 1.0 1.1680e-03 1.1 0.00e+00 0.0 3.6e+01 7.7e+01 8.0e+00  2  0  8  1 10   2  0  8  1 12     0
MatGetRowIJ            1 1.0 1.0967e-05 5.8 0.00e+00 0.0 0.0e+00 0.0e+00 0.0e+00  0  0  0  0  0   0  0  0  0  0     0
MatGetOrdering         1 1.0 4.8399e-04 8.4 0.00e+00 0.0 0.0e+00 0.0e+00 4.0e+00  0  0  0  0  5   0  0  0  0  6     0
MatZeroEntries         3 1.0 4.7207e-05 1.3 0.00e+00 0.0 0.0e+00 0.0e+00 0.0e+00  0  0  0  0  0   0  0  0  0  0     0
KSPGMRESOrthog        16 1.0 4.6160e-03 4.3 1.95e+05 1.3 0.0e+00 0.0e+00 1.6e+01  4  5  0  0 19   4  5  0  0 24   226
KSPSetup               2 1.0 7.7009e-05 1.4 0.00e+00 0.0 0.0e+00 0.0e+00 0.0e+00  0  0  0  0  0   0  0  0  0  0     0
KSPSolve               1 1.0 3.4800e-02 1.0 4.23e+06 1.6 3.1e+02 3.0e+02 3.9e+01 49100 69 46 46  49100 69 46 57   615
PCSetUp                2 1.0 1.3975e-02 3.3 2.33e+06 1.8 0.0e+00 0.0e+00 5.0e+00 11 54  0  0  6  11 54  0  0  7   820
PCSetUpOnBlocks        1 1.0 1.3752e-02 3.4 2.33e+06 1.8 0.0e+00 0.0e+00 5.0e+00 10 54  0  0  6  10 54  0  0  7   834
PCApply               17 1.0 3.3538e-03 5.2 1.30e+06 1.5 0.0e+00 0.0e+00 0.0e+00  2 32  0  0  0   2 32  0  0  0  2018
------------------------------------------------------------------------------------------------------------------------

Memory usage is given in bytes:

Object Type          Creations   Destructions     Memory  Descendants' Mem.
Reports information only for process 0.

--- Event Stage 0: Main Stage

                 Vec    33             33       124544     0
         Vec Scatter     2              2         1736     0
           Index Set     9              9        11144     0
   IS L to G Mapping     1              1         2316     0
              Matrix     4              4       603500     0
       Krylov Solver     2              2        18880     0
      Preconditioner     2              2         1408     0
========================================================================================================================
Average time to get PetscTime(): 0
Average time for MPI_Barrier(): 2.85625e-05
Average time for zero size MPI_Send(): 4.48227e-05
#PETSc Option Table entries:
-ksp_right_pc
-log_summary
-pc_type bjacobi
-sub_pc_factor_levels 4
-sub_pc_factor_zeropivot 0
-sub_pc_type ilu
#End of PETSc Option Table entries
Compiled without FORTRAN kernels
Compiled with full precision matrices (default)
sizeof(short) 2 sizeof(int) 4 sizeof(long) 8 sizeof(void*) 8 sizeof(PetscScalar) 8
Configure run at: Sat May 19 03:47:23 2012
Configure options: --with-debugging=false --COPTFLAGS=-O3 --CXXOPTFLAGS=-O3 --FOPTFLAGS=-O3 --with-clanguage=C++ --with-shared=1 --with-shared-libraries=1 --with-mpi-dir=/org/centers/pecos/LIBRARIES/MPICH2/mpich2-1.4.1-intel-11.1-lucid --with-mumps=true --download-mumps=1 --with-parmetis=true --download-parmetis=1 --with-superlu=true --download-superlu=1 --with-superludir=true --download-superlu_dist=1 --with-blacs=true --download-blacs=1 --with-scalapack=true --download-scalapack=1 --with-hypre=true --download-hypre=1 --with-blas-lib="[/org/centers/pecos/LIBRARIES/MKL/mkl-10.0.3.020-intel-11.1-lucid/lib/em64t/libmkl_intel_lp64.so,/org/centers/pecos/LIBRARIES/MKL/mkl-10.0.3.020-intel-11.1-lucid/lib/em64t/libmkl_sequential.so,/org/centers/pecos/LIBRARIES/MKL/mkl-10.0.3.020-intel-11.1-lucid/lib/em64t/libmkl_core.so]" --with-lapack-lib=/org/centers/pecos/LIBRARIES/MKL/mkl-10.0.3.020-intel-11.1-lucid/lib/em64t/libmkl_solver_lp64_sequential.a
-----------------------------------------
Libraries compiled on Sat May 19 03:47:23 CDT 2012 on daedalus 
Machine characteristics: Linux daedalus 2.6.32-34-generic #76-Ubuntu SMP Tue Aug 30 17:05:01 UTC 2011 x86_64 GNU/Linux 
Using PETSc directory: /org/centers/pecos/LIBRARIES/PETSC3/petsc-3.1-p5
Using PETSc arch: intel-11.1-lucid-mpich2-1.4.1-cxx-opt
-----------------------------------------
Using C compiler: /org/centers/pecos/LIBRARIES/MPICH2/mpich2-1.4.1-intel-11.1-lucid/bin/mpicxx -O3   -fPIC   
Using Fortran compiler: /org/centers/pecos/LIBRARIES/MPICH2/mpich2-1.4.1-intel-11.1-lucid/bin/mpif90 -fPIC -O3    
-----------------------------------------
Using include paths: -I/org/centers/pecos/LIBRARIES/PETSC3/petsc-3.1-p5/intel-11.1-lucid-mpich2-1.4.1-cxx-opt/include -I/org/centers/pecos/LIBRARIES/PETSC3/petsc-3.1-p5/include -I/org/centers/pecos/LIBRARIES/PETSC3/petsc-3.1-p5/intel-11.1-lucid-mpich2-1.4.1-cxx-opt/include -I/org/centers/pecos/LIBRARIES/MPICH2/mpich2-1.4.1-intel-11.1-lucid/include  
------------------------------------------
Using C linker: /org/centers/pecos/LIBRARIES/MPICH2/mpich2-1.4.1-intel-11.1-lucid/bin/mpicxx -O3 
Using Fortran linker: /org/centers/pecos/LIBRARIES/MPICH2/mpich2-1.4.1-intel-11.1-lucid/bin/mpif90 -fPIC -O3  
Using libraries: -Wl,-rpath,/org/centers/pecos/LIBRARIES/PETSC3/petsc-3.1-p5/intel-11.1-lucid-mpich2-1.4.1-cxx-opt/lib -L/org/centers/pecos/LIBRARIES/PETSC3/petsc-3.1-p5/intel-11.1-lucid-mpich2-1.4.1-cxx-opt/lib -lpetsc       -lX11 -Wl,-rpath,/org/centers/pecos/LIBRARIES/PETSC3/petsc-3.1-p5/intel-11.1-lucid-mpich2-1.4.1-cxx-opt/lib -L/org/centers/pecos/LIBRARIES/PETSC3/petsc-3.1-p5/intel-11.1-lucid-mpich2-1.4.1-cxx-opt/lib -lHYPRE -lcmumps -ldmumps -lsmumps -lzmumps -lmumps_common -lpord -lscalapack -lblacs -lsuperlu_dist_2.4 -lparmetis -lmetis -lsuperlu_4.0 -Wl,-rpath,/org/centers/pecos/LIBRARIES/MKL/mkl-10.0.3.020-intel-11.1-lucid/lib/em64t -L/org/centers/pecos/LIBRARIES/MKL/mkl-10.0.3.020-intel-11.1-lucid/lib/em64t -lmkl_solver_lp64_sequential -lmkl_intel_lp64 -lmkl_sequential -lmkl_core -ldl -Wl,-rpath,/org/centers/pecos/LIBRARIES/MPICH2/mpich2-1.4.1-intel-11.1-lucid/lib -L/org/centers/pecos/LIBRARIES/MPICH2/mpich2-1.4.1-intel-11.1-lucid/lib -lmpich -lopa -lmpl -lrt -lpthread -Wl,-rpath,/opt/intel/Compiler/11.1/073/lib/intel64 -L/opt/intel/Compiler/11.1/073/lib/intel64 -Wl,-rpath,/usr/lib/gcc/x86_64-linux-gnu/4.4.3 -L/usr/lib/gcc/x86_64-linux-gnu/4.4.3 -limf -lsvml -lipgo -ldecimal -lgcc_s -lirc -lirc_s -lmpichf90 -lifport -lifcore -lm -lm -lmpichcxx -lstdc++ -lmpichcxx -lstdc++ -ldl -lmpich -lopa -lmpl -lrt -lpthread -limf -lsvml -lipgo -ldecimal -lgcc_s -lirc -lirc_s -ldl  
------------------------------------------

-------------------------------------------------------------------
| Processor id:   0                                                |
| Num Processors: 6                                                |
| Time:           Fri Aug 24 15:28:17 2012                         |
| OS:             Linux                                            |
| HostName:       daedalus                                         |
| OS Release:     2.6.32-34-generic                                |
| OS Version:     #76-Ubuntu SMP Tue Aug 30 17:05:01 UTC 2011      |
| Machine:        x86_64                                           |
| Username:       roystgnr                                         |
| Configuration:  ./configure run on Wed Aug 22 12:44:06 CDT 2012  |
-------------------------------------------------------------------
 ----------------------------------------------------------------------------------------------------------------
| libMesh Performance: Alive time=0.132599, Active time=0.065285                                                 |
 ----------------------------------------------------------------------------------------------------------------
| Event                              nCalls    Total Time  Avg Time    Total Time  Avg Time    % of Active Time  |
|                                              w/o Sub     w/o Sub     With Sub    With Sub    w/o S    With S   |
|----------------------------------------------------------------------------------------------------------------|
|                                                                                                                |
|                                                                                                                |
| DofMap                                                                                                         |
|   add_neighbors_to_send_list()     1         0.0001      0.000053    0.0001      0.000072    0.08     0.11     |
|   build_sparsity()                 1         0.0007      0.000659    0.0008      0.000767    1.01     1.17     |
|   create_dof_constraints()         1         0.0003      0.000298    0.0003      0.000298    0.46     0.46     |
|   distribute_dofs()                1         0.0002      0.000199    0.0015      0.001467    0.30     2.25     |
|   dof_indices()                    365       0.0003      0.000001    0.0003      0.000001    0.48     0.48     |
|   prepare_send_list()              1         0.0000      0.000011    0.0000      0.000011    0.02     0.02     |
|   reinit()                         1         0.0003      0.000329    0.0003      0.000329    0.50     0.50     |
|                                                                                                                |
| EquationSystems                                                                                                |
|   build_solution_vector()          2         0.0009      0.000456    0.0015      0.000759    1.40     2.33     |
|                                                                                                                |
| ExodusII_IO                                                                                                    |
|   write_nodal_data()               1         0.0060      0.005964    0.0060      0.005964    9.14     9.14     |
|                                                                                                                |
| FE                                                                                                             |
|   compute_shape_functions()        49        0.0004      0.000008    0.0004      0.000008    0.60     0.60     |
|   init_shape_functions()           13        0.0001      0.000005    0.0001      0.000005    0.10     0.10     |
|   inverse_map()                    36        0.0001      0.000002    0.0001      0.000002    0.09     0.09     |
|                                                                                                                |
| FEMap                                                                                                          |
|   compute_affine_map()             49        0.0001      0.000001    0.0001      0.000001    0.10     0.10     |
|   compute_face_map()               12        0.0001      0.000004    0.0001      0.000010    0.08     0.18     |
|   init_face_shape_functions()      1         0.0000      0.000002    0.0000      0.000002    0.00     0.00     |
|   init_reference_to_physical_map() 13        0.0001      0.000007    0.0001      0.000007    0.13     0.13     |
|                                                                                                                |
| GMVIO                                                                                                          |
|   write_nodal_data()               1         0.0038      0.003759    0.0038      0.003759    5.76     5.76     |
|                                                                                                                |
| Mesh                                                                                                           |
|   find_neighbors()                 1         0.0002      0.000185    0.0002      0.000217    0.28     0.33     |
|   renumber_nodes_and_elem()        2         0.0000      0.000022    0.0000      0.000022    0.07     0.07     |
|                                                                                                                |
| MeshCommunication                                                                                              |
|   compute_hilbert_indices()        2         0.0013      0.000664    0.0013      0.000664    2.03     2.03     |
|   find_global_indices()            2         0.0002      0.000083    0.0047      0.002327    0.26     7.13     |
|   parallel_sort()                  2         0.0012      0.000575    0.0026      0.001317    1.76     4.03     |
|                                                                                                                |
| MeshOutput                                                                                                     |
|   write_equation_systems()         2         0.0000      0.000016    0.0113      0.005638    0.05     17.27    |
|                                                                                                                |
| MeshTools::Generation                                                                                          |
|   build_cube()                     1         0.0004      0.000426    0.0004      0.000426    0.65     0.65     |
|                                                                                                                |
| MetisPartitioner                                                                                               |
|   partition()                      1         0.0009      0.000855    0.0028      0.002762    1.31     4.23     |
|                                                                                                                |
| Parallel                                                                                                       |
|   allgather()                      8         0.0009      0.000118    0.0009      0.000118    1.45     1.45     |
|   broadcast()                      1         0.0000      0.000011    0.0000      0.000011    0.02     0.02     |
|   gather()                         1         0.0000      0.000005    0.0000      0.000005    0.01     0.01     |
|   max(scalar)                      2         0.0004      0.000180    0.0004      0.000180    0.55     0.55     |
|   max(vector)                      2         0.0002      0.000081    0.0002      0.000081    0.25     0.25     |
|   min(vector)                      2         0.0000      0.000016    0.0000      0.000016    0.05     0.05     |
|   probe()                          50        0.0022      0.000043    0.0022      0.000043    3.32     3.32     |
|   receive()                        50        0.0001      0.000002    0.0023      0.000045    0.13     3.46     |
|   send()                           50        0.0000      0.000001    0.0000      0.000001    0.08     0.08     |
|   send_receive()                   54        0.0001      0.000002    0.0024      0.000045    0.16     3.75     |
|   sum()                            13        0.0019      0.000149    0.0019      0.000149    2.96     2.96     |
|                                                                                                                |
| Parallel::Request                                                                                              |
|   wait()                           50        0.0000      0.000001    0.0000      0.000001    0.05     0.05     |
|                                                                                                                |
| Partitioner                                                                                                    |
|   set_node_processor_ids()         1         0.0001      0.000080    0.0017      0.001740    0.12     2.67     |
|   set_parent_processor_ids()       1         0.0000      0.000016    0.0000      0.000016    0.02     0.02     |
|                                                                                                                |
| PetscLinearSolver                                                                                              |
|   solve()                          1         0.0397      0.039727    0.0397      0.039727    60.85    60.85    |
|                                                                                                                |
| System                                                                                                         |
|   assemble()                       1         0.0022      0.002163    0.0029      0.002932    3.31     4.49     |
 ----------------------------------------------------------------------------------------------------------------
| Totals:                            848       0.0653                                          100.00            |
 ----------------------------------------------------------------------------------------------------------------

 
***************************************************************
* Done Running Example  mpirun -np 6 ./vector_fe_ex1-opt -pc_type bjacobi -sub_pc_type ilu -sub_pc_factor_levels 4 -sub_pc_factor_zeropivot 0 -ksp_right_pc -log_summary
***************************************************************
</pre>
</div>
<?php make_footer() ?>
</body>
</html>
<?php if (0) { ?>
\#Local Variables:
\#mode: html
\#End:
<?php } ?>
